<template>
    <div class="p-4 md:p-8">
        <!-- Header -->
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <Link :href="adminUrl('posts')" class="inline-flex items-center text-sm font-medium text-base-content/60 hover:text-primary mb-2 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    {{ t('blogAdmin.backToPosts') }}
                </Link>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-extrabold tracking-tight">{{ t('blogAdmin.editPost') }}</h1>
                    <span v-if="!post.is_translation" class="badge badge-outline mt-1">{{ t('blogAdmin.originalInLanguage', { language: post.language_label }) }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="submit" class="btn btn-primary" :disabled="form.processing">
                    <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                    {{ t('blogAdmin.saveChanges') }}
                </button>
            </div>
        </header>

        <div v-if="saved" role="status" class="mb-6 rounded-xl border border-success/30 bg-success/10 px-4 py-3 text-sm font-medium text-success">
            {{ t('blogAdmin.postSaved') }}
        </div>

        <!-- Two Column Layout -->
        <div class="flex flex-col lg:flex-row gap-8 items-start">
            
            <!-- Main Content Area (Left) -->
            <div class="flex-1 w-full min-w-0 flex flex-col gap-6">
                <!-- Title Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
                    <label class="block text-sm font-bold text-base-content mb-2">{{ t('blogAdmin.postTitle') }}</label>
                    <input v-model="form.title" type="text" :placeholder="t('blogAdmin.enterTitle')" class="w-full bg-base-200 border-0 rounded-xl px-4 py-3 text-lg font-medium focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all placeholder:font-normal placeholder:text-base-content/40" />
                    <div v-if="form.errors.title" class="text-error text-sm mt-2">{{ form.errors.title }}</div>
                </div>

                <!-- Markdown Editor Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 overflow-hidden flex flex-col h-[60vh] min-h-[400px] lg:h-[700px]">
                    <div class="px-6 py-4 border-b border-base-300 bg-base-100/50 flex justify-between items-center">
                        <h2 class="text-sm font-bold text-base-content">{{ t('blogAdmin.contentMarkdown') }}</h2>
                    </div>
                    <div class="flex-1 overflow-hidden relative">
                        <MdEditor 
                            v-model="form.content" 
                            :language="locale === 'zh-CN' ? 'zh-CN' : 'en-US'"
                            :theme="editorTheme" 
                            @onUploadImg="onUploadImg"
                            class="h-full !border-0"
                        />
                    </div>
                    <div v-if="form.errors.content" class="px-6 py-3 bg-error/10 text-error text-sm border-t border-error/20">{{ form.errors.content }}</div>
                </div>
            </div>

            <!-- Settings Sidebar (Right) -->
            <div class="w-full lg:w-80 flex-shrink-0 flex flex-col gap-6">
                <!-- Summary Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-5">
                    <label class="block text-sm font-bold text-base-content mb-2">{{ t('blogAdmin.summaryOptional') }}</label>
                    <textarea v-model="form.summary" rows="5" :placeholder="t('blogAdmin.summaryPlaceholder')" class="w-full bg-base-200 border-0 rounded-xl px-4 py-3 text-sm leading-6 focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all resize-none"></textarea>
                </div>

                <!-- Cover Image Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-5">
                    <h3 class="text-sm font-bold text-base-content mb-4 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ t('blogAdmin.coverImage') }}
                    </h3>
                    
                    <div v-if="coverImageUrl" class="relative group rounded-xl overflow-hidden mb-3 border border-base-300 aspect-video bg-base-200 flex items-center justify-center">
                        <img :src="coverImageUrl" class="object-cover w-full h-full" />
                        <div class="absolute inset-0 bg-base-content/60 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                            <button @click="removeCover" type="button" class="btn btn-sm btn-error text-white">{{ t('blogAdmin.remove') }}</button>
                        </div>
                    </div>
                    
                    <div class="relative border-2 border-dashed border-base-300 rounded-xl p-6 text-center hover:bg-base-200 transition-colors cursor-pointer group mb-3 aspect-video flex flex-col items-center justify-center" v-else>
                        <input type="file" @change="uploadCover" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                        <svg class="w-8 h-8 mx-auto text-base-content/40 group-hover:text-primary transition-colors mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <p class="text-xs text-base-content/60 font-medium">{{ t('blogAdmin.uploadCover') }}</p>
                    </div>
                    
                    <div v-if="uploadingCover" class="text-xs text-primary font-medium text-center flex items-center justify-center gap-2">
                        <span class="loading loading-spinner loading-xs"></span> {{ t('blogAdmin.uploading') }}
                    </div>
                </div>

                <!-- Organization Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-5">
                    <h3 class="text-sm font-bold text-base-content mb-5 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        {{ t('blogAdmin.organization') }}
                    </h3>
                    
                    <div class="space-y-4">
                        <div v-if="!post.is_translation">
                            <label class="block text-xs font-bold text-base-content/70 mb-1.5 uppercase tracking-wider">{{ t('blogAdmin.originalLanguage') }}</label>
                            <select v-model="form.language" class="w-full bg-base-200 border-0 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all">
                                <option v-for="(label, code) in locales" :key="code" :value="code">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-base-content/70 mb-1.5 uppercase tracking-wider">{{ t('blogAdmin.category') }}</label>
                            <select v-model="form.category_id" class="w-full bg-base-200 border-0 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all">
                                <option :value="null">{{ t('blogAdmin.uncategorized') }}</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name[locale] || Object.values(category.name)[0] }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-base-content/70 mb-1.5 uppercase tracking-wider">{{ t('blogAdmin.tags') }}</label>
                            <div class="rounded-lg bg-base-200 p-2">
                                <div v-if="selectedTags.length" class="mb-2 flex flex-wrap gap-1.5">
                                    <span v-for="tag in selectedTags" :key="tag.id" class="inline-flex items-center gap-1 rounded-md bg-base-100 px-2 py-1 text-xs font-medium shadow-sm">
                                        {{ tagLabel(tag) }}
                                        <button type="button" @click="toggleTag(tag.id)" :aria-label="`${t('blogAdmin.remove')} ${tagLabel(tag)}`" class="rounded text-base-content/50 transition-colors hover:text-error focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
                                        </button>
                                    </span>
                                </div>
                                <div class="relative" @focusout="closeTagPicker">
                                    <input v-model="tagSearch" type="search" :placeholder="t('blogAdmin.searchTags')" @focus="tagPickerOpen = true" class="w-full rounded-md border-0 bg-base-100 px-3 py-2 text-sm focus:ring-2 focus:ring-primary" />
                                    <div v-if="tagPickerOpen" class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-md bg-base-100 p-1 shadow-lg ring-1 ring-base-300">
                                        <button v-for="tag in filteredTags" :key="tag.id" type="button" @click="toggleTag(tag.id)" class="flex w-full items-center justify-between rounded px-2.5 py-2 text-left text-sm transition-colors hover:bg-base-200 active:scale-[0.98]">
                                            <span>{{ tagLabel(tag) }}</span>
                                            <svg v-if="form.tag_ids.includes(tag.id)" class="size-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6" /></svg>
                                        </button>
                                        <p v-if="filteredTags.length === 0" class="px-2.5 py-3 text-sm text-base-content/60">{{ t('blogAdmin.noMatchingTags') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm" aria-labelledby="translations-heading">
                    <div class="mb-4">
                        <h3 id="translations-heading" class="text-sm font-bold text-base-content">{{ t('blogAdmin.translations') }}</h3>
                        <p class="mt-1 text-xs leading-5 text-base-content/60">{{ t('blogAdmin.translationHelp') }}</p>
                        <p v-if="form.isDirty" class="mt-2 text-xs font-medium text-warning">{{ t('blogAdmin.saveBeforeCreatingTranslation') }}</p>
                    </div>

                    <div class="space-y-2">
                        <template v-for="([code, label]) in translationOptions" :key="code">
                            <Link v-if="translation(code)" :href="adminUrl(`posts/${translation(code)?.id}/edit`)" class="flex min-h-10 items-center justify-between rounded-lg bg-base-200 px-3 text-sm font-medium transition-colors [@media(hover:hover)]:hover:bg-base-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary active:scale-[0.98]">
                                <span>{{ label }}</span>
                                <span class="text-xs text-base-content/60">{{ t('blogAdmin.edit') }}</span>
                            </Link>
                            <button v-else type="button" class="flex min-h-10 w-full items-center justify-between rounded-lg border border-dashed border-base-300 px-3 text-left text-sm font-medium text-primary transition-colors [@media(hover:hover)]:hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary active:scale-[0.98] disabled:cursor-wait disabled:opacity-60" :disabled="form.processing || form.isDirty" @click="createTranslation(code)">
                                <span>{{ label }}</span>
                                <span class="text-xs">{{ t('blogAdmin.createTranslation') }}</span>
                            </button>
                        </template>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, useForm, useHttp } from '@inertiajs/vue3';
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

const props = withDefaults(defineProps<{
    post: Post;
    categories: Category[];
    tags: Tag[];
    locales: Record<string, string>;
    translations: Array<{ id: number; language: string }>;
}>(), { translations: () => [] });

const { adminUrl, apiUrl } = useBlogRoutes();
const translation = (language: string) => (props.translations ?? []).find((item) => item.language === language);
const createTranslation = (language: string) => form.post(adminUrl(`posts/${props.post.id}/translations/${language}`));
const translationOptions = computed(() => Object.entries(props.locales).filter(([language]) => language !== props.post.language));

const form = useForm({
    title: props.post.title || '',
    summary: props.post.summary || '',
    content: props.post.content || '',
    category_id: props.post.category ? props.post.category.id : null as number | null,
    tag_ids: props.post.tags ? props.post.tags.map((t: Tag) => t.id) : [] as number[],
    language: props.post.language || 'en',
    cover_image_id: props.post.cover_image ? props.post.cover_image.id : null as number | null,
});

const coverImageUrl = ref<string | null>(props.post.cover_image ? props.post.cover_image.url : null);
const uploadingCover = ref(false);
const saved = ref(false);
const tagSearch = ref('');
const tagPickerOpen = ref(false);
const uploadHttp = useHttp({
    file: null as File | null,
});

const editorTheme = computed(() => {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
});

const tagLabel = (tag: Tag): string => tag.name[locale.value] || Object.values(tag.name)[0];

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
        const attachment = await uploadAttachment(file);
        
        form.cover_image_id = attachment.id;
        coverImageUrl.value = attachment.url;
    } catch (error) {
        console.error('Upload failed:', error);
        alert(t('blogAdmin.uploadFailed'));
    } finally {
        uploadingCover.value = false;
        target.value = ''; 
    }
};

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

const submit = () => {
    form.put(adminUrl(`posts/${props.post.id}`), {
        preserveScroll: true,
        onSuccess: () => {
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
    --md-bk-color: transparent !important;
    --md-border-color: transparent !important;
}
.dark .md-editor {
    --md-bk-color: transparent !important;
    --md-color: var(--color-base-content) !important;
}
</style>
