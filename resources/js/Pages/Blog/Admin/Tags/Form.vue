<template>
    <div class="p-4 md:p-8">
        <header class="mb-8 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">{{ isEditing ? t('blogAdmin.taxonomy.editTag') : t('blogAdmin.taxonomy.newTag') }}</h1>
                <p class="mt-1 font-medium text-base-content/60">{{ t('blogAdmin.taxonomy.tagTranslations') }}</p>
            </div>
            <Link :href="adminUrl('tags')" class="btn btn-ghost">{{ t('blogAdmin.taxonomy.backToTags') }}</Link>
        </header>

        <form @submit.prevent="submit">
            <section class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-sm">
                <div class="border-b border-base-300 px-6 py-5">
                    <h2 class="font-bold">{{ t('blogAdmin.taxonomy.tagTranslations') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] border-collapse text-left">
                        <thead>
                            <tr class="border-b border-base-300 bg-base-200/50 text-xs font-bold uppercase tracking-wider text-base-content/70">
                                <th class="w-40 px-6 py-4">{{ t('blogAdmin.common.language') }}</th>
                                <th class="px-6 py-4">{{ t('blogAdmin.taxonomy.name') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200/80">
                            <tr v-for="(label, code) in locales" :key="code">
                                <td class="px-6 py-4 font-semibold">{{ label }}</td>
                                <td class="px-6 py-4 align-top">
                                    <input v-model="form.name[code]" type="text" :required="code === defaultLocale" :placeholder="t('blogAdmin.taxonomy.tagNameExample', { locale: label })" class="w-full rounded-xl border-0 bg-base-200 px-4 py-3 text-sm font-medium focus:bg-base-100 focus:ring-2 focus:ring-primary" />
                                    <div v-if="form.errors[`name.${code}`]" class="mt-1.5 text-xs font-medium text-error">{{ form.errors[`name.${code}`] }}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="mt-6 flex justify-end gap-3">
                <Link :href="adminUrl('tags')" class="btn btn-ghost">{{ t('blogAdmin.common.cancel') }}</Link>
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
import type { Tag } from '../../types';
import Layout from '../Layout.vue';
import { useBlogRoutes } from '../../admin-routes';

defineOptions({ layout: Layout });

const { t } = useI18n();
const { adminUrl } = useBlogRoutes();
const props = defineProps<{ tag?: Tag; locales: Record<string, string> }>();
const isEditing = computed(() => props.tag !== undefined);
const defaultLocale = Object.keys(props.locales)[0];

const form = useForm({
    name: Object.fromEntries(Object.keys(props.locales).map((code) => [code, props.tag?.name[code] ?? ''])),
});

const submit = () => {
    if (props.tag) {
        form.put(adminUrl(`tags/${props.tag.id}`));

        return;
    }

    form.post(adminUrl('tags'));
};
</script>
