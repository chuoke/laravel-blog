<template>
    <div class="p-4 md:p-8">
        <header class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">{{ t('blogAdmin.categories') }}</h1>
                <p class="text-base-content/60 mt-1 font-medium">{{ t('blogAdmin.manageCategories') }}</p>
            </div>
        </header>

        <div class="flex flex-col md:flex-row gap-8 items-start">
            <!-- Categories List (Left) -->
            <div class="flex-1 w-full bg-base-100 rounded-3xl shadow-sm border border-base-300 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-base-200/50 border-b border-base-300 text-xs uppercase tracking-wider text-base-content/70 font-bold">
                                <th class="px-6 py-5 rounded-tl-3xl">{{ t('blogAdmin.categoryInfo') }}</th>
                                <th class="px-6 py-5">{{ t('blogAdmin.slug') }}</th>
                                <th class="px-6 py-5 text-center">{{ t('blogAdmin.sortOrder') }}</th>
                                <th class="px-6 py-5 rounded-tr-3xl text-right">{{ t('blogAdmin.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200/80">
                            <tr v-for="category in categories" :key="category.id" class="hover:bg-base-200/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[15px] group-hover:text-primary transition-colors">{{ category.name[Object.keys(locales)[0]] || Object.values(category.name)[0] }}</div>
                                    <div class="text-xs text-base-content/50 mt-1 truncate max-w-xs">{{ category.description?.[Object.keys(locales)[0]] || Object.values(category.description || {})[0] || t('blogAdmin.noDescription') }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-base-content/60">
                                    <span class="px-2 py-1 bg-base-200 rounded-md">{{ category.slug }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <span class="font-bold text-base-content/70">{{ category.sort_order }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200">
                                        <button @click="editCategory(category)" class="p-2 text-base-content/40 hover:text-primary hover:bg-primary/10 rounded-xl transition-all" :title="t('blogAdmin.edit')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button @click="deleteCategory(category.id)" class="p-2 text-base-content/40 hover:text-error hover:bg-error/10 rounded-xl transition-all" :title="t('blogAdmin.delete')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="categories.length === 0">
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-base-200 rounded-2xl flex items-center justify-center mb-3">
                                            <svg class="w-8 h-8 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        </div>
                                        <p class="text-sm font-bold text-base-content/80">{{ t('blogAdmin.noCategoriesFound') }}</p>
                                        <p class="text-xs text-base-content/50 mt-1">{{ t('blogAdmin.createFirstCategory') }}</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form Panel (Right) -->
            <div ref="formPanel" class="w-full md:w-80 bg-base-100 rounded-3xl shadow-sm border border-base-300 p-6 flex-shrink-0 md:sticky md:top-8">
                <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
                    <span v-if="editing" class="w-2 h-6 rounded-full bg-warning"></span>
                    <span v-else class="w-2 h-6 rounded-full bg-primary"></span>
                    {{ editing ? t('blogAdmin.editCategory') : t('blogAdmin.newCategory') }}
                </h2>
                
                <form @submit.prevent="submitForm">
                    <div class="mb-4" v-for="(label, code) in locales" :key="'name_'+code">
                        <label class="block text-xs font-bold text-base-content/70 mb-2 uppercase tracking-wider">{{ t('blogAdmin.nameWithLocale', { locale: label }) }}</label>
                        <input v-model="form.name[code]" type="text" :placeholder="t('blogAdmin.categoryNameExample', { locale: label })" class="w-full bg-base-200 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all font-medium" :required="code === Object.keys(locales)[0]" />
                        <div v-if="form.errors[`name.${code}`]" class="text-error text-xs mt-1.5 font-medium">{{ form.errors[`name.${code}`] }}</div>
                    </div>
                    
                    <div class="mb-4" v-for="(label, code) in locales" :key="'desc_'+code">
                        <label class="block text-xs font-bold text-base-content/70 mb-2 uppercase tracking-wider">{{ t('blogAdmin.descriptionWithLocale', { locale: label }) }}</label>
                        <textarea v-model="form.description[code]" rows="2" :placeholder="t('blogAdmin.categoryDescriptionExample', { locale: label })" class="w-full bg-base-200 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all resize-none"></textarea>
                        <div v-if="form.errors[`description.${code}`]" class="text-error text-xs mt-1.5 font-medium">{{ form.errors[`description.${code}`] }}</div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs font-bold text-base-content/70 mb-2 uppercase tracking-wider">{{ t('blogAdmin.sortOrder') }}</label>
                        <input v-model="form.sort_order" type="number" class="w-full bg-base-200 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all font-medium" />
                        <div v-if="form.errors.sort_order" class="text-error text-xs mt-1.5 font-medium">{{ form.errors.sort_order }}</div>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary flex-1 shadow-sm" :disabled="form.processing">
                            <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                            {{ editing ? t('blogAdmin.saveChanges') : t('blogAdmin.create') }}
                        </button>
                        <button v-if="editing" type="button" @click="cancelEdit" class="btn btn-outline border-base-300 text-base-content hover:bg-base-200 hover:border-base-300">
                            {{ t('blogAdmin.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import type { Category } from '../../types';
import Layout from '../Layout.vue';
import { useBlogRoutes } from '../../admin-routes';

defineOptions({ layout: Layout });

const { t } = useI18n();
const { adminUrl } = useBlogRoutes();

const props = defineProps<{
    categories: Category[];
    locales: Record<string, string>;
}>();

const editing = ref(false);
const editingId = ref<number | null>(null);
const formPanel = ref<HTMLElement | null>(null);

const initForm = () => {
    const defaultName: Record<string, string> = {};
    const defaultDesc: Record<string, string> = {};
    for (const code in props.locales) {
        defaultName[code] = '';
        defaultDesc[code] = '';
    }
    return {
        name: defaultName,
        description: defaultDesc,
        sort_order: 0,
    };
};

const form = useForm(initForm());

const submitForm = () => {
    if (editing.value && editingId.value) {
        form.put(adminUrl(`categories/${editingId.value}`), {
            preserveScroll: true,
            onSuccess: () => cancelEdit(),
        });
    } else {
        form.post(adminUrl('categories'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }
};

const editCategory = (category: Category) => {
    editing.value = true;
    editingId.value = category.id;
    
    for (const code in props.locales) {
        form.name[code] = category.name?.[code] || '';
        form.description[code] = category.description?.[code] || '';
    }
    form.sort_order = category.sort_order || 0;
    form.clearErrors();

    // The form sits below the list on small screens — bring it into view
    if (window.innerWidth < 768) {
        formPanel.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

const cancelEdit = () => {
    editing.value = false;
    editingId.value = null;
    form.defaults(initForm());
    form.reset();
    form.clearErrors();
};

const deleteCategory = (id: number) => {
    if (confirm(t('blogAdmin.deleteCategoryConfirm'))) {
        router.delete(adminUrl(`categories/${id}`), {
            preserveScroll: true,
            onSuccess: () => {
                if (editingId.value === id) {
                    cancelEdit();
                }
            }
        });
    }
};
</script>
