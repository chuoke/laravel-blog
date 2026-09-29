<template>
    <div class="p-4 md:p-8">
        <!-- Header -->
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <Link :href="adminUrl('posts')" class="inline-flex items-center text-sm font-medium text-base-content/60 hover:text-primary mb-2 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    {{ t('blogAdmin.posts.back') }}
                </Link>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-extrabold tracking-tight">{{ t('blogAdmin.posts.edit') }}</h1>
                    <span v-if="!post.is_translation" class="badge badge-outline mt-1">{{ t('blogAdmin.posts.translations.originalInLanguage', { language: post.language_label }) }}</span>
                    <span v-else class="badge badge-primary badge-outline mt-1">{{ t('blogAdmin.posts.translations.editingInLanguage', { language: post.language_label }) }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button v-if="aiEnabled && post.is_translation" type="button" class="btn btn-ghost btn-sm" :disabled="translationHttp.processing" @click="translateContent">
                    <span v-if="translationHttp.processing" class="loading loading-spinner loading-xs"></span>
                    {{ translationHttp.processing ? t('blogAdmin.ai.translating') : t('blogAdmin.ai.translate') }}
                </button>
                <button v-if="hasMultipleLocales" type="button" class="btn btn-ghost btn-sm" :aria-expanded="translationsExpanded" aria-controls="translation-options" @click="translationsExpanded = !translationsExpanded">
                    {{ t('blogAdmin.posts.translations.title') }}
                    <svg class="size-4 transition-transform" :class="{ 'rotate-180': translationsExpanded }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg>
                </button>
                <button v-if="post.status === 'draft'" type="button" @click="submit()" class="btn btn-outline border-base-300 text-base-content hover:bg-base-300 hover:border-base-300" :disabled="form.processing">
                    {{ t('blogAdmin.posts.saveDraft') }}
                </button>
                <button type="button" @click="submit(post.status === 'draft' ? 'published' : undefined)" class="btn btn-primary" :disabled="form.processing">
                    <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                    {{ post.status === 'draft' ? t('blogAdmin.posts.publish') : t('blogAdmin.common.saveChanges') }}
                </button>
            </div>
        </header>

        <section v-if="translationsExpanded && hasMultipleLocales" id="translation-options" class="mb-6 rounded-xl border border-base-300 bg-base-100 p-4 shadow-sm">
            <div class="mb-3">
                <p class="mt-1 text-xs leading-5 text-base-content/60">
                    {{ post.is_translation ? t('blogAdmin.posts.translations.fromLanguage', { language: originalLanguageLabel }) : t('blogAdmin.posts.translations.help') }}
                </p>
                <p v-if="form.isDirty" class="mt-2 text-xs font-medium text-warning">{{ t('blogAdmin.posts.translations.saveBeforeCreating') }}</p>
            </div>

            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                <template v-for="([code, label]) in translationOptions" :key="code">
                    <div v-if="translation(code)" class="flex min-h-10 items-center rounded-lg bg-base-200 text-sm font-medium">
                        <Link :href="adminUrl(`posts/${translation(code)?.id}/edit`)" class="flex flex-1 items-center justify-between px-3 transition-colors [@media(hover:hover)]:hover:bg-base-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary active:scale-[0.98]">
                            <span>{{ label }}</span>
                            <span class="text-xs text-base-content/60">{{ t('blogAdmin.common.edit') }}</span>
                        </Link>
                        <button type="button" class="mr-1.5 rounded-md p-1.5 text-base-content/50 transition-colors hover:bg-error/10 hover:text-error focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" :aria-label="t('blogAdmin.posts.delete')" @click="deleteTranslation(translation(code)!)">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6" /></svg>
                        </button>
                    </div>
                    <button v-else type="button" class="flex min-h-10 w-full items-center justify-between rounded-lg border border-dashed border-base-300 px-3 text-left text-sm font-medium text-primary transition-colors [@media(hover:hover)]:hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary active:scale-[0.98] disabled:cursor-wait disabled:opacity-60" :disabled="form.processing || form.isDirty" @click="createTranslation(code)">
                        <span>{{ label }}</span>
                        <span class="text-xs">{{ t('blogAdmin.posts.translations.create') }}</span>
                    </button>
                </template>
            </div>
        </section>

        <p v-if="aiDependencyMissing" class="mb-6 rounded-xl border border-warning/30 bg-warning/10 px-4 py-3 text-sm text-warning-content">{{ t('blogAdmin.ai.dependencyMissing') }}</p>

        <div v-if="saved" role="status" class="mb-6 rounded-xl border border-success/30 bg-success/10 px-4 py-3 text-sm font-medium text-success">
            {{ t('blogAdmin.posts.saved') }}
        </div>

        <!-- Two Column Layout -->
        <div class="flex flex-col lg:flex-row gap-8 items-start">

            <!-- Main Content Area (Left) -->
            <div class="flex-1 w-full min-w-0 flex flex-col gap-6">
                <!-- Title Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
                    <label class="block text-sm font-bold text-base-content mb-2">{{ t('blogAdmin.posts.title') }}</label>
                    <input v-model="form.title" type="text" :placeholder="t('blogAdmin.posts.enterTitle')" class="w-full bg-base-200 border-0 rounded-xl px-4 py-3 text-lg font-medium focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all placeholder:font-normal placeholder:text-base-content/40" />
                    <div v-if="form.errors.title" class="text-error text-sm mt-2">{{ form.errors.title }}</div>
                </div>

                <!-- Markdown Editor Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 overflow-hidden flex flex-col h-[60vh] min-h-[400px] lg:h-[700px]">
                    <div class="px-6 py-4 border-b border-base-300 bg-base-100/50 flex justify-between items-center">
                        <h2 class="text-sm font-bold text-base-content">{{ t('blogAdmin.posts.contentMarkdown') }}</h2>
                    </div>
                    <div class="relative min-h-0 flex-1 overflow-hidden">
                        <MdEditor
                            v-model="form.content"
                            height="100%"
                            :language="locale === 'zh-CN' ? 'zh-CN' : 'en-US'"
                            :theme="editorTheme"
                            @onUploadImg="onUploadImg"
                            class="h-full! border-0!"
                        />
                    </div>
                    <div v-if="form.errors.content" class="px-6 py-3 bg-error/10 text-error text-sm border-t border-error/20">{{ form.errors.content }}</div>
                </div>
            </div>

            <!-- Settings Sidebar (Right) -->
            <div class="w-full lg:w-80 shrink-0 flex flex-col gap-6">
                <!-- Summary Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-5">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label class="block text-sm font-bold text-base-content">{{ t('blogAdmin.posts.summaryOptional') }}</label>
                        <button v-if="aiEnabled" type="button" class="btn btn-ghost btn-xs" :disabled="summaryHttp.processing" @click="generateSummary">
                            <span v-if="summaryHttp.processing" class="loading loading-spinner loading-xs"></span>
                            {{ summaryHttp.processing ? t('blogAdmin.ai.generating') : t('blogAdmin.ai.generate') }}
                        </button>
                    </div>
                    <textarea v-model="form.summary" rows="5" :placeholder="t('blogAdmin.posts.summaryPlaceholder')" class="w-full bg-base-200 border-0 rounded-xl px-4 py-3 text-sm leading-6 focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all resize-none"></textarea>
                </div>

                <!-- Cover Image Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-5">
                    <h3 class="text-sm font-bold text-base-content mb-4 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ t('blogAdmin.posts.coverImage') }}
                    </h3>

                    <div v-if="coverImageUrl" class="relative group rounded-xl overflow-hidden mb-3 border border-base-300 aspect-video bg-base-200 flex items-center justify-center">
                        <img :src="coverImageUrl" class="object-cover w-full h-full" />
                        <div class="absolute inset-0 bg-base-content/60 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                            <button @click="removeCover" type="button" class="btn btn-sm btn-error text-white">{{ t('blogAdmin.posts.remove') }}</button>
                        </div>
                    </div>

                    <div class="relative border-2 border-dashed border-base-300 rounded-xl p-6 text-center hover:bg-base-200 transition-colors cursor-pointer group mb-3 aspect-video flex flex-col items-center justify-center" v-else>
                        <input type="file" @change="uploadCover" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                        <svg class="w-8 h-8 mx-auto text-base-content/40 group-hover:text-primary transition-colors mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <p class="text-xs text-base-content/60 font-medium">{{ t('blogAdmin.posts.uploadCover') }}</p>
                    </div>

                    <div v-if="uploadingCover" class="text-xs text-primary font-medium text-center flex items-center justify-center gap-2">
                        <span class="loading loading-spinner loading-xs"></span> {{ t('blogAdmin.ai.coverProcessing') }}
                    </div>
                    <button v-if="aiEnabled" type="button" class="btn btn-ghost btn-sm mt-3 w-full" :disabled="coverHttp.processing" @click="generateCover">
                        <span v-if="coverHttp.processing" class="loading loading-spinner loading-xs"></span>
                        {{ coverHttp.processing ? t('blogAdmin.ai.generating') : t('blogAdmin.ai.generateCover') }}
                    </button>
                </div>

                <div v-if="aiEnabled" class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-base-content">{{ t('blogAdmin.ai.review') }}</h3>
                            <p class="mt-1 text-xs leading-5 text-base-content/60">{{ t('blogAdmin.ai.reviewHelp') }}</p>
                        </div>
                        <button type="button" class="btn btn-ghost btn-sm shrink-0" :disabled="reviewHttp.processing" @click="reviewContent">
                            <span v-if="reviewHttp.processing" class="loading loading-spinner loading-xs"></span>
                            {{ reviewHttp.processing ? t('blogAdmin.ai.reviewing') : t('blogAdmin.ai.runReview') }}
                        </button>
                    </div>
                    <div v-if="reviewResult" class="mt-4 border-t border-base-300 pt-4">
                        <div class="flex items-center justify-between gap-3"><span class="text-sm font-bold tabular-nums">{{ reviewResult.score }}/100</span><span class="badge" :class="reviewDecisionClass">{{ reviewDecisionLabel }}</span></div>
                        <p class="mt-3 text-sm leading-6 text-base-content/75">{{ reviewResult.summary }}</p>
                        <div v-if="reviewResult.strengths.length" class="mt-3"><p class="text-xs font-bold uppercase tracking-wider text-base-content/60">{{ t('blogAdmin.ai.strengths') }}</p><ul class="mt-1.5 space-y-1 text-sm leading-5 text-success"><li v-for="strength in reviewResult.strengths" :key="strength">{{ strength }}</li></ul></div>
                        <div v-if="reviewResult.issues.length" class="mt-3"><p class="text-xs font-bold uppercase tracking-wider text-base-content/60">{{ t('blogAdmin.ai.issues') }}</p><ul class="mt-1.5 space-y-1 text-sm leading-5 text-base-content/75"><li v-for="issue in reviewResult.issues" :key="issue">{{ issue }}</li></ul></div>
                    </div>
                </div>

                <!-- Organization Card -->
                <div v-if="!post.is_translation" class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-5">
                    <h3 class="text-sm font-bold text-base-content mb-5 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        {{ t('blogAdmin.posts.organization') }}
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-base-content/70 mb-1.5 uppercase tracking-wider">{{ t('blogAdmin.posts.translations.originalLanguage') }}</label>
                            <select v-model="form.language" class="w-full bg-base-200 border-0 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all">
                                <option v-for="(label, code) in locales" :key="code" :value="code">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-base-content/70 mb-1.5 uppercase tracking-wider">{{ t('blogAdmin.posts.category') }}</label>
                            <select v-model="form.category_id" class="w-full bg-base-200 border-0 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all">
                                <option :value="null">{{ t('blogAdmin.posts.uncategorized') }}</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name[taxonomyLocale] || Object.values(category.name)[0] }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-base-content/70 mb-1.5 uppercase tracking-wider">{{ t('blogAdmin.navigation.tags') }}</label>
                            <div class="rounded-lg bg-base-200 p-2">
                                <div v-if="selectedTags.length" class="mb-2 flex flex-wrap gap-1.5">
                                    <span v-for="tag in selectedTags" :key="tag.id" class="inline-flex items-center gap-1 rounded-md bg-base-100 px-2 py-1 text-xs font-medium shadow-sm">
                                        {{ tagLabel(tag) }}
                                        <button type="button" @click="toggleTag(tag.id)" :aria-label="`${t('blogAdmin.posts.remove')} ${tagLabel(tag)}`" class="rounded text-base-content/50 transition-colors hover:text-error focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
                                        </button>
                                    </span>
                                </div>
                                <div class="relative" @focusout="closeTagPicker">
                                    <input v-model="tagSearch" type="search" :placeholder="t('blogAdmin.posts.searchTags')" @focus="tagPickerOpen = true" class="w-full rounded-md border-0 bg-base-100 px-3 py-2 text-sm focus:ring-2 focus:ring-primary" />
                                    <div v-if="tagPickerOpen" class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-md bg-base-100 p-1 shadow-lg ring-1 ring-base-300">
                                        <button v-for="tag in filteredTags" :key="tag.id" type="button" @click="toggleTag(tag.id)" class="flex w-full items-center justify-between rounded px-2.5 py-2 text-left text-sm transition-colors hover:bg-base-200 active:scale-[0.98]">
                                            <span>{{ tagLabel(tag) }}</span>
                                            <svg v-if="form.tag_ids.includes(tag.id)" class="size-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6" /></svg>
                                        </button>
                                        <p v-if="filteredTags.length === 0" class="px-2.5 py-3 text-sm text-base-content/60">{{ t('blogAdmin.posts.noMatchingTags') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, useForm, useHttp } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Layout from '../Layout.vue';

defineOptions({ layout: Layout });

const { t, locale } = useI18n();

// Import md-editor-v3
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import type { Post, Category, Tag } from '../../types';
import { useBlogRoutes } from '../../admin-routes';

type AttachmentResponse = {
    id: number;
    url: string;
};

type ReviewResult = {
    score: number;
    decision: 'ready' | 'needs_revision' | 'high_risk';
    summary: string;
    strengths: string[];
    issues: string[];
};

const props = withDefaults(defineProps<{
    post: Post;
    categories: Category[];
    tags: Tag[];
    locales: Record<string, string>;
    translations: Array<{ id: number; language: string }>;
    originalLanguage: string;
    aiEnabled: boolean;
    aiDependencyMissing: boolean;
}>(), { translations: () => [] });

const { adminUrl, apiUrl } = useBlogRoutes();
const translation = (language: string) => (props.translations ?? []).find((item) => item.language === language);
const createTranslation = (language: string) => form.post(adminUrl(`posts/${props.post.id}/translations/${language}`));
const deleteTranslation = (translation: { id: number; language: string }): void => {
    if (confirm(t('blogAdmin.posts.deleteConfirm'))) {
        router.delete(adminUrl(`posts/${translation.id}`));
    }
};
const translationOptions = computed(() => Object.entries(props.locales).filter(([language]) => language !== props.post.language));
const hasMultipleLocales = computed(() => translationOptions.value.length > 0);
const originalLanguageLabel = computed(() => props.locales[props.originalLanguage] ?? props.originalLanguage);

const form = useForm({
    title: props.post.title || '',
    summary: props.post.summary || '',
    content: props.post.content || '',
    category_id: props.post.category ? props.post.category.id : null as number | null,
    tag_ids: props.post.tags ? props.post.tags.map((t: Tag) => t.id) : [] as number[],
    language: props.post.language || 'en',
    cover_image_id: props.post.cover_image ? props.post.cover_image.id : null as number | null,
    status: null as 'published' | null,
});

const coverImageUrl = ref<string | null>(props.post.cover_image ? props.post.cover_image.url : null);
const uploadingCover = ref(false);
const saved = ref(false);
const translationsExpanded = ref(false);
const tagSearch = ref('');
const tagPickerOpen = ref(false);
const uploadHttp = useHttp({
    file: null as File | null,
});
const coverUploadHttp = useHttp({ file: null as File | null });
const summaryHttp = useHttp({ title: '', content: '', language: '' });
const coverHttp = useHttp({ title: '', content: '', language: '' });
const reviewHttp = useHttp({ title: '', content: '', language: '' });
const translationHttp = useHttp({});
const reviewResult = ref<ReviewResult | null>(null);

const editorTheme = computed(() => {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
});

const taxonomyLocale = computed(() => document.documentElement.lang.replace('-', '_'));

const tagLabel = (tag: Tag): string => tag.name[taxonomyLocale.value] || Object.values(tag.name)[0];

const selectedTags = computed(() => props.tags.filter((tag) => form.tag_ids.includes(tag.id)));

const filteredTags = computed(() => {
    const search = tagSearch.value.trim().toLocaleLowerCase();

    if (! search) {
        return props.tags;
    }

    return props.tags.filter((tag) => tagLabel(tag).toLocaleLowerCase().includes(search));
});

const toggleTag = (tagId: number): void => {
    form.tag_ids = form.tag_ids.includes(tagId)
        ? form.tag_ids.filter((id) => id !== tagId)
        : [...form.tag_ids, tagId];
    tagSearch.value = '';
};

const closeTagPicker = (event: FocusEvent): void => {
    if (! (event.currentTarget as HTMLElement).contains(event.relatedTarget as Node | null)) {
        tagPickerOpen.value = false;
    }
};

const uploadCover = async (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    uploadingCover.value = true;
    try {
        const attachment = await uploadCoverAttachment(file);

        form.cover_image_id = attachment.id;
        coverImageUrl.value = attachment.url;
    } catch (error) {
        console.error('Upload failed:', error);
        alert(t('blogAdmin.posts.uploadFailed'));
    } finally {
        uploadingCover.value = false;
        target.value = '';
    }
};

const fillAiContent = (request: { title: string; content: string; language: string }): void => {
    request.title = form.title;
    request.content = form.content;
    request.language = form.language;
};

const generateSummary = async (): Promise<void> => {
    fillAiContent(summaryHttp);

    try {
        const result = await summaryHttp.post(adminUrl('ai/summary')) as { summary: string };
        form.summary = result.summary;
    } catch {
        alert(t('blogAdmin.ai.summaryFailed'));
    }
};

const generateCover = async (): Promise<void> => {
    fillAiContent(coverHttp);

    try {
        const attachment = await coverHttp.post(adminUrl('ai/cover')) as AttachmentResponse;
        form.cover_image_id = attachment.id;
        coverImageUrl.value = attachment.url;
    } catch {
        alert(t('blogAdmin.ai.coverFailed'));
    }
};

const reviewContent = async (): Promise<void> => {
    fillAiContent(reviewHttp);

    try {
        reviewResult.value = await reviewHttp.post(adminUrl('ai/review')) as ReviewResult;
    } catch {
        alert(t('blogAdmin.ai.reviewFailed'));
    }
};

const translateContent = async (): Promise<void> => {
    try {
        const translation = await translationHttp.post(adminUrl(`posts/${props.post.id}/ai/translate`)) as { title: string; summary: string; content: string };
        form.title = translation.title;
        form.summary = translation.summary;
        form.content = translation.content;
    } catch {
        alert(t('blogAdmin.ai.translationFailed'));
    }
};

const reviewDecisionLabel = computed(() => {
    if (reviewResult.value?.decision === 'ready') return t('blogAdmin.ai.ready');
    if (reviewResult.value?.decision === 'high_risk') return t('blogAdmin.ai.highRisk');

    return t('blogAdmin.ai.needsRevision');
});

const reviewDecisionClass = computed(() => ({
    'badge-success': reviewResult.value?.decision === 'ready',
    'badge-warning': reviewResult.value?.decision === 'needs_revision',
    'badge-error': reviewResult.value?.decision === 'high_risk',
}));

const removeCover = () => {
    form.cover_image_id = null;
    coverImageUrl.value = null;
};

const onUploadImg = async (files: File[], callback: (urls: string[]) => void) => {
    const urls: string[] = [];

    for (const file of files) {
        const attachment = await uploadAttachment(file);
        urls.push(attachment.url);
    }

    callback(urls);
};

const uploadAttachment = async (file: File): Promise<AttachmentResponse> => {
    uploadHttp.file = file;

    const attachment = await uploadHttp.post(apiUrl('attachments')) as AttachmentResponse;
    uploadHttp.file = null;

    return attachment;
};

const uploadCoverAttachment = async (file: File): Promise<AttachmentResponse> => {
    coverUploadHttp.file = file;

    const attachment = await coverUploadHttp.post(adminUrl('cover-upload')) as AttachmentResponse;
    coverUploadHttp.file = null;

    return attachment;
};

const submit = (status?: 'published') => {
    form.status = status ?? null;

    form.put(adminUrl(`posts/${props.post.id}`), {
        preserveScroll: true,
        onSuccess: () => {
            form.status = null;
            form.defaults();
            saved.value = true;
            setTimeout(() => {
                saved.value = false;
            }, 3000);
        },
    });
};
</script>

<style>
/* Adjust md-editor-v3 to fit our theme nicely */
.md-editor {
    /* --md-bk-color: transparent !important; */
    --md-border-color: transparent !important;
}
.dark .md-editor {
    /* --md-bk-color: transparent !important; */
    --md-color: var(--color-base-content) !important;
}
</style>
