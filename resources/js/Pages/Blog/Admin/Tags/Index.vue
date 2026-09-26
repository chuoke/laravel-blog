<template>
    <div class="p-4 md:p-8">
        <header class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">{{ t('blogAdmin.tags') }}</h1>
                <p class="text-base-content/60 mt-1 font-medium">{{ t('blogAdmin.manageTags') }}</p>
            </div>
        </header>

        <div class="flex flex-col md:flex-row gap-8 items-start">
            <!-- Tags Grid (Left) -->
            <div class="flex-1 w-full">
                <div v-if="tags.length === 0" class="bg-base-100 rounded-3xl p-16 shadow-sm border border-base-300 text-center">
                    <div class="w-16 h-16 bg-base-200 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    </div>
                    <p class="text-sm font-bold text-base-content/80">{{ t('blogAdmin.noTagsFound') }}</p>
                    <p class="text-xs text-base-content/50 mt-1">{{ t('blogAdmin.createFirstTag') }}</p>
                </div>
                
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="tag in tags" :key="tag.id" class="bg-base-100 rounded-2xl p-5 shadow-sm border border-base-300 group hover:shadow-md hover:border-primary/30 transition-all flex justify-between items-start">
                        <div>
                            <div class="font-bold text-base group-hover:text-primary transition-colors flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary/40 group-hover:bg-primary transition-colors"></span>
                                {{ tag.name[Object.keys(locales)[0]] || Object.values(tag.name)[0] }}
                            </div>
                            <div class="flex items-center gap-2 mt-1.5">
                                <div class="text-xs text-base-content/50 px-2 py-0.5 bg-base-200 rounded">{{ tag.slug }}</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-1 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                            <button @click="editTag(tag)" class="p-1.5 text-base-content/40 hover:text-primary hover:bg-primary/10 rounded-lg transition-all" :title="t('blogAdmin.edit')">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <button @click="deleteTag(tag.id)" class="p-1.5 text-base-content/40 hover:text-error hover:bg-error/10 rounded-lg transition-all" :title="t('blogAdmin.delete')">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Panel (Right) -->
            <div ref="formPanel" class="w-full md:w-80 bg-base-100 rounded-3xl shadow-sm border border-base-300 p-6 flex-shrink-0 md:sticky md:top-8">
                <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
                    <span v-if="editing" class="w-2 h-6 rounded-full bg-warning"></span>
                    <span v-else class="w-2 h-6 rounded-full bg-primary"></span>
                    {{ editing ? t('blogAdmin.editTag') : t('blogAdmin.newTag') }}
                </h2>
                
                <form @submit.prevent="submitForm">
                    <div class="mb-6" v-for="(label, code) in locales" :key="code">
                        <label class="block text-xs font-bold text-base-content/70 mb-2 uppercase tracking-wider">{{ t('blogAdmin.nameWithLocale', { locale: label }) }}</label>
                        <input v-model="form.name[code]" type="text" :placeholder="t('blogAdmin.tagNameExample', { locale: label })" class="w-full bg-base-200 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all font-medium" :required="code === Object.keys(locales)[0]" />
                        <div v-if="form.errors[`name.${code}`]" class="text-error text-xs mt-1.5 font-medium">{{ form.errors[`name.${code}`] }}</div>
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
import type { Tag } from '../../types';
import Layout from '../Layout.vue';
import { useBlogRoutes } from '../../admin-routes';

defineOptions({ layout: Layout });

const { t } = useI18n();
const { adminUrl } = useBlogRoutes();

const props = defineProps<{
    tags: Tag[];
    locales: Record<string, string>;
}>();

const editing = ref(false);
const editingId = ref<number | null>(null);
const formPanel = ref<HTMLElement | null>(null);

const initForm = () => {
    const defaultName: Record<string, string> = {};
    for (const code in props.locales) {
        defaultName[code] = '';
    }
    return { name: defaultName };
};

const form = useForm(initForm());

const submitForm = () => {
    if (editing.value && editingId.value) {
        form.put(adminUrl(`tags/${editingId.value}`), {
            preserveScroll: true,
            onSuccess: () => cancelEdit(),
        });
    } else {
        form.post(adminUrl('tags'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }
};

const editTag = (tag: Tag) => {
    editing.value = true;
    editingId.value = tag.id;
    for (const code in props.locales) {
        form.name[code] = tag.name?.[code] || '';
    }
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

const deleteTag = (id: number) => {
    if (confirm(t('blogAdmin.deleteTagConfirm'))) {
        router.delete(adminUrl(`tags/${id}`), {
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
