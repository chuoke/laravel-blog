<template>
    <div class="p-4 md:p-8">
        <header class="mb-8 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">{{ t('blogAdmin.navigation.tags') }}</h1>
                <p class="mt-1 font-medium text-base-content/60">{{ t('blogAdmin.taxonomy.manageTags') }}</p>
            </div>
            <Link :href="adminUrl('tags/create')" class="btn btn-primary shrink-0">{{ t('blogAdmin.taxonomy.newTag') }}</Link>
        </header>

        <div class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-base-300 bg-base-200/50 text-xs font-bold uppercase tracking-wider text-base-content/70">
                            <th class="px-6 py-5">{{ t('blogAdmin.taxonomy.tag') }}</th>
                            <th class="px-6 py-5">{{ t('blogAdmin.taxonomy.slug') }}</th>
                            <th class="px-6 py-5 text-right">{{ t('blogAdmin.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200/80">
                        <tr v-for="tag in tags" :key="tag.id" class="group transition-colors hover:bg-base-200/30">
                            <td class="px-6 py-4 text-[15px] font-bold transition-colors group-hover:text-primary">{{ tag.name[taxonomyLocale] || Object.values(tag.name)[0] }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-base-content/60"><span class="rounded-md bg-base-200 px-2 py-1">{{ tag.slug }}</span></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="adminUrl(`tags/${tag.id}/edit`)" class="rounded-xl p-2 text-base-content/40 transition-colors hover:bg-primary/10 hover:text-primary" :title="t('blogAdmin.common.edit')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </Link>
                                    <button type="button" @click="deleteTag(tag.id)" class="rounded-xl p-2 text-base-content/40 transition-colors hover:bg-error/10 hover:text-error" :title="t('blogAdmin.common.delete')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="tags.length === 0">
                            <td colspan="3" class="px-6 py-16 text-center">
                                <p class="text-sm font-bold text-base-content/80">{{ t('blogAdmin.taxonomy.noTagsFound') }}</p>
                                <p class="mt-1 text-xs text-base-content/50">{{ t('blogAdmin.taxonomy.createFirstTag') }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import type { Tag } from '../../types';
import Layout from '../Layout.vue';
import { useBlogRoutes } from '../../admin-routes';

defineOptions({ layout: Layout });

const { t } = useI18n();
const taxonomyLocale = computed(() => document.documentElement.lang.replace('-', '_'));
const { adminUrl } = useBlogRoutes();

defineProps<{ tags: Tag[] }>();

const deleteTag = (id: number) => {
    if (confirm(t('blogAdmin.taxonomy.deleteTagConfirm'))) {
        router.delete(adminUrl(`tags/${id}`));
    }
};
</script>
