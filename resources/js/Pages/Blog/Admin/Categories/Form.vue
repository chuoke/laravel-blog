<template>
    <div class="p-4 md:p-8">
        <header class="mb-8 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">{{ isEditing ? t('blogAdmin.taxonomy.editCategory') : t('blogAdmin.taxonomy.newCategory') }}</h1>
                <p class="mt-1 font-medium text-base-content/60">{{ t('blogAdmin.taxonomy.categoryTranslations') }}</p>
            </div>
            <Link :href="adminUrl('categories')" class="btn btn-ghost">{{ t('blogAdmin.taxonomy.backToCategories') }}</Link>
        </header>

        <form @submit.prevent="submit">
            <section class="mb-6 rounded-3xl border border-base-300 bg-base-100 p-6 shadow-sm">
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-base-content/70">{{ t('blogAdmin.taxonomy.sortOrder') }}</label>
                <input v-model="form.sort_order" type="number" class="w-full max-w-xs rounded-xl border-0 bg-base-200 px-4 py-3 text-sm font-medium focus:bg-base-100 focus:ring-2 focus:ring-primary" />
                <div v-if="form.errors.sort_order" class="mt-1.5 text-xs font-medium text-error">{{ form.errors.sort_order }}</div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-sm">
                <div class="border-b border-base-300 px-6 py-5">
                    <h2 class="font-bold">{{ t('blogAdmin.taxonomy.categoryTranslations') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] table-fixed border-collapse text-left">
                        <thead>
                            <tr class="border-b border-base-300 bg-base-200/50 text-xs font-bold uppercase tracking-wider text-base-content/70">
                                <th class="w-32 px-6 py-4">{{ t('blogAdmin.common.language') }}</th>
                                <th class="w-64 px-6 py-4">{{ t('blogAdmin.taxonomy.name') }}</th>
                                <th class="px-6 py-4">{{ t('blogAdmin.taxonomy.description') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200/80">
                            <tr v-for="(label, code) in locales" :key="code">
                                <td class="px-6 py-4 font-semibold">{{ label }}</td>
                                <td class="px-6 py-4 align-top">
                                    <input v-model="form.name[code]" type="text" :required="code === defaultLocale" :placeholder="t('blogAdmin.taxonomy.categoryNameExample', { locale: label })" class="w-full rounded-xl border-0 bg-base-200 px-4 py-3 text-sm font-medium focus:bg-base-100 focus:ring-2 focus:ring-primary" />
                                    <div v-if="form.errors[`name.${code}`]" class="mt-1.5 text-xs font-medium text-error">{{ form.errors[`name.${code}`] }}</div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <textarea v-model="form.description[code]" rows="2" :placeholder="t('blogAdmin.taxonomy.categoryDescriptionExample', { locale: label })" class="w-full resize-none rounded-xl border-0 bg-base-200 px-4 py-3 text-sm focus:bg-base-100 focus:ring-2 focus:ring-primary"></textarea>
                                    <div v-if="form.errors[`description.${code}`]" class="mt-1.5 text-xs font-medium text-error">{{ form.errors[`description.${code}`] }}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="mt-6 flex justify-end gap-3">
                <Link :href="adminUrl('categories')" class="btn btn-ghost">{{ t('blogAdmin.common.cancel') }}</Link>
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                    {{ isEditing ? t('blogAdmin.common.saveChanges') : t('blogAdmin.common.create') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import type { Category } from '../../types';
import Layout from '../Layout.vue';
import { useBlogRoutes } from '../../admin-routes';

defineOptions({ layout: Layout });

const { t } = useI18n();
const { adminUrl } = useBlogRoutes();
const props = defineProps<{ category?: Category; locales: Record<string, string> }>();
const isEditing = computed(() => props.category !== undefined);
const defaultLocale = Object.keys(props.locales)[0];

const form = useForm({
    name: Object.fromEntries(Object.keys(props.locales).map((code) => [code, props.category?.name[code] ?? ''])),
    description: Object.fromEntries(Object.keys(props.locales).map((code) => [code, props.category?.description?.[code] ?? ''])),
    sort_order: props.category?.sort_order ?? 0,
});

const submit = () => {
    if (props.category) {
        form.put(adminUrl(`categories/${props.category.id}`));

        return;
    }

    form.post(adminUrl('categories'));
};
</script>
