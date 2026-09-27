<template>
    <div class="p-4 md:p-8">
        <!-- Header Section -->
        <header class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight bg-gradient-to-br from-primary to-secondary bg-clip-text text-transparent">
                    {{ t('blogAdmin.posts') }}
                </h1>
                <p class="mt-2 text-base-content/70 font-medium">{{ t('blogAdmin.managePosts') }}</p>
            </div>
            <div>
                <Link :href="adminUrl('posts/create')" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-semibold text-primary-content bg-primary hover:bg-primary/90 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 group">
                    <svg class="w-5 h-5 mr-2 -ml-1 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    {{ t('blogAdmin.createPost') }}
                </Link>
            </div>
        </header>

        <!-- Glassmorphic Filter Bar -->
        <section class="mb-8 p-3 md:p-4 rounded-2xl bg-base-100/70 backdrop-blur-xl border border-base-300 shadow-sm flex flex-wrap gap-4 items-center justify-between">
            <div class="flex flex-wrap items-center gap-4 flex-1">
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input v-model="form.search" type="text" :placeholder="t('blogAdmin.searchTitles')" class="block w-full pl-10 pr-3 py-2 border-0 ring-1 ring-base-300 bg-base-100/50 rounded-xl text-sm placeholder-base-content/40 focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all shadow-sm">
                </div>
                
                <select v-model="form.status" class="py-2 pl-3 pr-8 border-0 ring-1 ring-base-300 bg-base-100/50 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all text-base-content/80 shadow-sm">
                    <option value="">{{ t('blogAdmin.allStatuses') }}</option>
                    <option value="draft">{{ t('blogAdmin.drafts') }}</option>
                    <option value="published">{{ t('blogAdmin.published') }}</option>
                    <option value="archived">{{ t('blogAdmin.archived') }}</option>
                </select>
                
                <select v-model="form.language" class="py-2 pl-3 pr-8 border-0 ring-1 ring-base-300 bg-base-100/50 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all text-base-content/80 shadow-sm">
                    <option value="">{{ t('blogAdmin.allLanguages') }}</option>
                    <option v-for="(label, code) in locales" :key="code" :value="code">{{ label }}</option>
                </select>

                <select v-model="form.sort_by" :aria-label="t('blogAdmin.sortBy')" class="py-2 pl-3 pr-8 border-0 ring-1 ring-base-300 bg-base-100/50 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all text-base-content/80 shadow-sm">
                    <option value="id">{{ t('blogAdmin.sortDefault') }}</option>
                    <option value="updated_at">{{ t('blogAdmin.sortUpdatedAt') }}</option>
                    <option value="published_at">{{ t('blogAdmin.sortPublishedAt') }}</option>
                    <option value="view_count">{{ t('blogAdmin.sortViewCount') }}</option>
                </select>
            </div>

            <!-- Toggle Originals -->
            <div class="flex flex-wrap items-center gap-4">
            <label class="flex items-center gap-3 cursor-pointer group px-2">
                <span class="text-sm font-medium text-base-content/80 group-hover:text-base-content transition-colors">{{ t('blogAdmin.originalsOnly') }}</span>
                <div class="relative">
                    <input type="checkbox" v-model="form.origin_only" class="sr-only">
                    <div class="block bg-base-300 w-10 h-6 rounded-full transition-colors duration-300" :class="{'bg-primary': form.origin_only}"></div>
                    <div class="dot absolute left-1 top-1 bg-base-100 w-4 h-4 rounded-full transition-transform duration-300 shadow-sm" :class="{'transform translate-x-4': form.origin_only}"></div>
                </div>
            </label>
            <label class="flex items-center gap-3 cursor-pointer group px-2">
                <span class="text-sm font-medium text-base-content/80 group-hover:text-base-content transition-colors">{{ t('blogAdmin.pinnedOnly') }}</span>
                <div class="relative">
                    <input type="checkbox" v-model="form.pinned_only" class="sr-only">
                    <div class="block bg-base-300 w-10 h-6 rounded-full transition-colors duration-300" :class="{'bg-primary': form.pinned_only}"></div>
                    <div class="dot absolute left-1 top-1 bg-base-100 w-4 h-4 rounded-full transition-transform duration-300 shadow-sm" :class="{'transform translate-x-4': form.pinned_only}"></div>
                </div>
            </label>
            </div>
        </section>

        <!-- Main Data Grid -->
        <div class="bg-base-100 rounded-3xl shadow-sm border border-base-300 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-base-200/80 border-b border-base-300 text-xs uppercase tracking-wider text-base-content/70 font-bold">
                            <th class="px-6 py-5 rounded-tl-3xl">{{ t('blogAdmin.postDetails') }}</th>
                            <th class="px-6 py-5">{{ t('blogAdmin.status') }}</th>
                            <th class="px-6 py-5">{{ t('blogAdmin.engagement') }}</th>
                            <th class="px-6 py-5">{{ t('blogAdmin.dates') }}</th>
                            <th class="px-6 py-5 rounded-tr-3xl text-right">{{ t('blogAdmin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        <tr v-for="post in posts.data" :key="post.id" class="hover:bg-base-200/50 transition-colors group">
                            <td class="px-6 py-4 min-w-[300px]">
                                <div class="flex items-center gap-4">
                                    <div class="h-14 w-14 rounded-2xl bg-base-200 shrink-0 overflow-hidden border border-base-300 flex items-center justify-center shadow-sm">
                                        <img v-if="post.cover_image" :src="post.cover_image.url" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                                        <svg class="w-6 h-6 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-base-content text-[15px] group-hover:text-primary transition-colors flex items-center gap-2 truncate">
                                            {{ post.title }}
                                            <span v-if="post.is_pinned" :title="t('blogAdmin.pinnedPost')" class="text-warning shrink-0">
                                                <svg class="w-4 h-4 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                                            </span>
                                        </div>
                                        <div class="text-xs text-base-content/70 mt-1.5 flex gap-2 items-center">
                                            <span class="px-2 py-0.5 rounded-md bg-base-100 border border-base-300 shadow-sm font-semibold text-base-content/80">{{ post.language_label }}</span>
                                            <span v-if="post.is_pinned" class="inline-flex items-center gap-1 rounded-md bg-warning/15 px-2 py-0.5 font-semibold text-warning">
                                                <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                                                {{ t('blogAdmin.pinned') }}
                                            </span>
                                            <span v-if="post.category" class="font-medium text-primary cursor-pointer transition-colors">{{ post.category.name[post.language] ?? Object.values(post.category.name)[0] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border shadow-sm"
                                    :class="{
                                        'bg-success/10 text-success border-success/20': post.status === 'published',
                                        'bg-base-200 text-base-content/70 border-base-300': post.status === 'draft',
                                        'bg-error/10 text-error border-error/20': post.status === 'archived'
                                    }">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="{
                                            'bg-success/100 shadow-[0_0_8px_rgba(16,185,129,0.8)]': post.status === 'published',
                                            'bg-base-content/40': post.status === 'draft',
                                            'bg-error/100': post.status === 'archived'
                                        }"></span>
                                    <span>{{ t(`blogAdmin.status${post.status.charAt(0).toUpperCase()}${post.status.slice(1)}`) }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-sm font-medium text-base-content/80">
                                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-base-200 rounded-lg border border-base-200">
                                        <svg class="w-4 h-4 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        {{ post.view_count || 0 }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="text-base-content font-medium">{{ post.published_at ? new Date(post.published_at).toLocaleDateString() : t('blogAdmin.notPublished') }}</div>
                                <div class="text-base-content/50 text-xs mt-0.5">{{ t('blogAdmin.createdAt', { date: new Date(post.created_at).toLocaleDateString() }) }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                                    <Link :href="adminUrl(`posts/${post.id}/edit`)" class="p-2 text-base-content/50 hover:text-primary hover:bg-primary/10 rounded-xl transition-all" :title="t('blogAdmin.editPost')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </Link>
                                    <button @click="togglePin(post.id)" class="p-2 rounded-xl transition-all" :class="post.is_pinned ? 'text-warning hover:bg-warning/10' : 'text-base-content/50 hover:text-warning hover:bg-warning/10'" :title="post.is_pinned ? t('blogAdmin.unpinPost') : t('blogAdmin.pinPost')">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                                    </button>
                                    <button @click="deletePost(post.id)" class="p-2 text-base-content/50 hover:text-error hover:bg-error/10 rounded-xl transition-all" :title="t('blogAdmin.deletePost')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Empty State -->
                        <tr v-if="!posts.data || posts.data.length === 0">
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-base-200 border border-base-200 rounded-2xl flex items-center justify-center mb-4 shadow-sm">
                                        <svg class="w-8 h-8 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    </div>
                                    <h3 class="text-base font-bold text-base-content">{{ t('blogAdmin.noPostsFound') }}</h3>
                                    <p class="text-sm text-base-content/70 mt-1 max-w-sm mx-auto">{{ t('blogAdmin.createFirstPost') }}</p>
                                    <Link :href="adminUrl('posts/create')" class="mt-6 text-sm font-semibold text-primary bg-primary/10 hover:bg-primary/20 px-4 py-2 rounded-lg transition-colors">
                                        {{ t('blogAdmin.createPost') }}
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Footer -->
            <div v-if="posts.links && posts.links.length > 3" class="px-6 py-4 border-t border-base-300 bg-base-200/50 flex flex-col sm:flex-row gap-4 items-center justify-between">
                <span class="text-sm text-base-content/70 font-medium">
                    {{ t('blogAdmin.showingResults', { from: posts.from || 0, to: posts.to || 0, total: posts.total }) }}
                </span>
                <div class="flex items-center gap-1.5 shadow-sm rounded-lg overflow-hidden border border-base-300 bg-base-100">
                    <template v-for="(link, index) in posts.links" :key="index">
                        <Link 
                            v-if="link.url"
                            :href="link.url" 
                            class="px-3.5 py-2 text-sm font-semibold transition-colors border-r last:border-r-0 border-base-200"
                            :class="link.active ? 'bg-primary/10 text-primary' : 'text-base-content/80 hover:bg-base-200 hover:text-base-content'"
                        ><span v-html="link.label"></span></Link>
                        <span 
                            v-else 
                            class="px-3.5 py-2 text-sm text-base-content/30 font-semibold border-r last:border-r-0 border-base-200 cursor-not-allowed bg-base-200/50"
                            v-html="link.label"
                        ></span>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import type { Post, PaginatedData } from '../../types';
import Layout from '../Layout.vue';
import { useBlogRoutes } from '../../admin-routes';

defineOptions({ layout: Layout });

const { t } = useI18n();
const { adminUrl } = useBlogRoutes();

const props = defineProps<{
    posts: PaginatedData<Post>;
    filters: Record<string, any>;
    locales: Record<string, string>;
}>();

const form = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    language: props.filters?.language || '',
    origin_only: props.filters?.origin_only !== '0' && props.filters?.origin_only !== false,
    pinned_only: props.filters?.pinned_only === true || props.filters?.pinned_only === '1',
    sort_by: props.filters?.sort_by || 'id',
});

let timeout: ReturnType<typeof setTimeout>;
watch(form, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(adminUrl('posts'), value as any, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
}, { deep: true });

const deletePost = (id: number) => {
    if (confirm(t('blogAdmin.deletePostConfirm'))) {
        router.delete(adminUrl(`posts/${id}`), {
            preserveScroll: true,
        });
    }
};

const togglePin = (id: number) => {
    router.put(adminUrl(`posts/${id}/pin`), {}, {
        preserveScroll: true,
    });
};
</script>
