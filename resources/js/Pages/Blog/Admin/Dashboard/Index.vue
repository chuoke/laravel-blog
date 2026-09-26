<template>
    <div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6 md:space-y-8 pb-20">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">{{ t('blogAdmin.dashboard') }}</h1>
                <p class="text-base-content/60 mt-1 font-medium">{{ t('blogAdmin.overview') }}</p>
            </div>
            <Link :href="adminUrl('posts/create')" class="btn btn-primary shadow-sm hover:shadow-md transition-all">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ t('blogAdmin.writePost') }}
            </Link>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Posts -->
            <div class="bg-base-100 rounded-3xl p-6 border border-base-300 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/5 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
                <div class="flex justify-between items-start mb-4 relative">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15"></path></svg>
                    </div>
                    <span class="badge badge-sm badge-ghost font-bold text-base-content/60">{{ stats.published_posts }} {{ t('blogAdmin.published') }}</span>
                </div>
                <div class="relative">
                    <h3 class="text-4xl font-extrabold">{{ stats.total_posts }}</h3>
                    <p class="text-sm font-medium text-base-content/60 mt-1 uppercase tracking-wider">{{ t('blogAdmin.totalPosts') }}</p>
                </div>
            </div>

            <!-- Total Views -->
            <div class="bg-base-100 rounded-3xl p-6 border border-base-300 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-secondary/5 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
                <div class="flex justify-between items-start mb-4 relative">
                    <div class="w-12 h-12 bg-secondary/10 text-secondary rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </div>
                </div>
                <div class="relative">
                    <h3 class="text-4xl font-extrabold">{{ stats.total_views.toLocaleString() }}</h3>
                    <p class="text-sm font-medium text-base-content/60 mt-1 uppercase tracking-wider">{{ t('blogAdmin.totalViews') }}</p>
                </div>
            </div>

            <!-- Drafts -->
            <div class="bg-base-100 rounded-3xl p-6 border border-base-300 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-warning/5 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
                <div class="flex justify-between items-start mb-4 relative">
                    <div class="w-12 h-12 bg-warning/10 text-warning rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                </div>
                <div class="relative">
                    <h3 class="text-4xl font-extrabold">{{ stats.draft_posts }}</h3>
                    <p class="text-sm font-medium text-base-content/60 mt-1 uppercase tracking-wider">{{ t('blogAdmin.drafts') }}</p>
                </div>
            </div>

            <!-- Taxonomy -->
            <div class="bg-base-100 rounded-3xl p-6 border border-base-300 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-accent/5 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
                <div class="flex justify-between items-start mb-4 relative">
                    <div class="w-12 h-12 bg-accent/10 text-accent rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    </div>
                </div>
                <div class="relative flex items-end gap-3">
                    <div>
                        <h3 class="text-3xl font-extrabold">{{ stats.total_categories }}</h3>
                        <p class="text-xs font-medium text-base-content/60 mt-1 uppercase tracking-wider">{{ t('blogAdmin.categories') }}</p>
                    </div>
                    <div class="w-px h-10 bg-base-300"></div>
                    <div>
                        <h3 class="text-3xl font-extrabold">{{ stats.total_tags }}</h3>
                        <p class="text-xs font-medium text-base-content/60 mt-1 uppercase tracking-wider">{{ t('blogAdmin.tags') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lists Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Trending Posts -->
            <div class="bg-base-100 rounded-3xl border border-base-300 overflow-hidden shadow-sm flex flex-col">
                <div class="p-6 border-b border-base-200 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <h2 class="text-lg font-bold">{{ t('blogAdmin.trendingPosts') }}</h2>
                </div>
                <div class="p-0 flex-1">
                    <ul class="divide-y divide-base-200">
                        <li v-for="(post, index) in topPosts" :key="post.id" class="p-4 hover:bg-base-200/50 transition-colors flex items-center gap-4">
                            <div class="text-2xl font-black text-base-content/10 w-8 text-center">{{ index + 1 }}</div>
                            <div class="flex-1 min-w-0">
                                <Link :href="adminUrl(`posts/${post.id}/edit`)" class="font-bold text-sm hover:text-primary transition-colors block truncate">{{ post.title }}</Link>
                                <div class="text-xs text-base-content/60 mt-1 flex items-center gap-2">
                                    <span v-if="post.category" class="font-medium text-primary">{{ post.category.name[post.language] || Object.values(post.category.name)[0] }}</span>
                                    <span v-if="post.category">&bull;</span>
                                    <span>{{ new Date(post.published_at || post.created_at).toLocaleDateString() }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold">{{ post.view_count.toLocaleString() }}</div>
                                <div class="text-[10px] uppercase text-base-content/50 font-bold tracking-wider mt-0.5">{{ t('blogAdmin.views') }}</div>
                            </div>
                        </li>
                        <li v-if="topPosts.length === 0" class="p-8 text-center text-base-content/50 font-medium">{{ t('blogAdmin.noPostsAvailable') }}</li>
                    </ul>
                </div>
            </div>

            <!-- Recent Posts -->
            <div class="bg-base-100 rounded-3xl border border-base-300 overflow-hidden shadow-sm flex flex-col">
                <div class="p-6 border-b border-base-200 flex items-center gap-3 justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold">{{ t('blogAdmin.recentActivity') }}</h2>
                    </div>
                    <Link :href="adminUrl('posts')" class="text-xs font-bold text-primary hover:underline">{{ t('blogAdmin.viewAll') }}</Link>
                </div>
                <div class="p-0 flex-1">
                    <ul class="divide-y divide-base-200">
                        <li v-for="post in recentPosts" :key="post.id" class="p-4 hover:bg-base-200/50 transition-colors flex items-center gap-4">
                            <div class="w-2 h-2 rounded-full" :class="post.status === 'published' ? 'bg-success' : 'bg-warning'"></div>
                            <div class="flex-1 min-w-0">
                                <Link :href="adminUrl(`posts/${post.id}/edit`)" class="font-bold text-sm hover:text-primary transition-colors block truncate">{{ post.title }}</Link>
                                <div class="text-xs text-base-content/60 mt-1 flex items-center gap-2">
                                    <span class="badge badge-xs font-bold uppercase" :class="post.status === 'published' ? 'badge-success badge-outline' : 'badge-warning badge-outline'">{{ post.status }}</span>
                                    <span>&bull;</span>
                                    <span>{{ new Date(post.created_at).toLocaleString() }}</span>
                                </div>
                            </div>
                        </li>
                        <li v-if="recentPosts.length === 0" class="p-8 text-center text-base-content/50 font-medium">{{ t('blogAdmin.noRecentActivity') }}</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Layout from '../Layout.vue';
import { useBlogRoutes } from '../../admin-routes';
import { Post } from '../../types';

defineOptions({ layout: Layout });

const { t } = useI18n();
const { adminUrl } = useBlogRoutes();

defineProps<{
    stats: {
        total_posts: number;
        published_posts: number;
        draft_posts: number;
        total_views: number;
        total_categories: number;
        total_tags: number;
    };
    recentPosts: Post[];
    topPosts: Post[];
}>();
</script>
