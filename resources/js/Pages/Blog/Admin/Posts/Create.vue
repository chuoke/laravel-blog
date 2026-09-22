<template>
    <div class="p-4 md:p-8">
        <!-- Header -->
        <header class="mb-8 flex items-center justify-between">
            <div>
                <Link href="/admin/blog/posts" class="inline-flex items-center text-sm font-medium text-base-content/60 hover:text-primary mb-2 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Posts
                </Link>
                <h1 class="text-3xl font-extrabold tracking-tight">Create New Post</h1>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="submit('draft')" class="btn btn-outline border-base-300 text-base-content hover:bg-base-300 hover:border-base-300">
                    Save Draft
                </button>
                <button type="button" @click="submit('published')" class="btn btn-primary" :disabled="form.processing">
                    <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                    Publish Post
                </button>
            </div>
        </header>

        <!-- Two Column Layout -->
        <div class="flex flex-col lg:flex-row gap-8 items-start">
            
            <!-- Main Content Area (Left) -->
            <div class="flex-1 w-full flex flex-col gap-6">
                <!-- Title & Summary Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-base-content mb-2">Post Title</label>
                        <input v-model="form.title" type="text" placeholder="Enter an engaging title..." class="w-full bg-base-200 border-0 rounded-xl px-4 py-3 text-lg font-medium focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all placeholder:font-normal placeholder:text-base-content/40" />
                        <div v-if="form.errors.title" class="text-error text-sm mt-2">{{ form.errors.title }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-base-content mb-2">Summary (Optional)</label>
                        <textarea v-model="form.summary" rows="2" placeholder="Brief description of the post..." class="w-full bg-base-200 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all resize-none"></textarea>
                    </div>
                </div>

                <!-- Markdown Editor Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 overflow-hidden flex flex-col h-[700px]">
                    <div class="px-6 py-4 border-b border-base-300 bg-base-100/50 flex justify-between items-center">
                        <h2 class="text-sm font-bold text-base-content">Content (Markdown)</h2>
                        <!-- md-editor-v3 theme toggler could go here if implemented -->
                    </div>
                    <div class="flex-1 overflow-hidden relative">
                        <MdEditor 
                            v-model="form.content" 
                            language="en-US" 
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
                <!-- Cover Image Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-5">
                    <h3 class="text-sm font-bold text-base-content mb-4 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Cover Image
                    </h3>
                    
                    <div v-if="coverImageUrl" class="relative group rounded-xl overflow-hidden mb-3 border border-base-300 aspect-video bg-base-200 flex items-center justify-center">
                        <img :src="coverImageUrl" class="object-cover w-full h-full" />
                        <div class="absolute inset-0 bg-base-content/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                            <button @click="removeCover" type="button" class="btn btn-sm btn-error text-white">Remove</button>
                        </div>
                    </div>
                    
                    <div class="relative border-2 border-dashed border-base-300 rounded-xl p-6 text-center hover:bg-base-200 transition-colors cursor-pointer group mb-3 aspect-video flex flex-col items-center justify-center" v-else>
                        <input type="file" @change="uploadCover" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                        <svg class="w-8 h-8 mx-auto text-base-content/40 group-hover:text-primary transition-colors mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <p class="text-xs text-base-content/60 font-medium">Click or drag to upload</p>
                    </div>
                    
                    <div v-if="uploadingCover" class="text-xs text-primary font-medium text-center flex items-center justify-center gap-2">
                        <span class="loading loading-spinner loading-xs"></span> Uploading...
                    </div>
                </div>

                <!-- Organization Card -->
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-5">
                    <h3 class="text-sm font-bold text-base-content mb-5 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Organization
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-base-content/70 mb-1.5 uppercase tracking-wider">Category</label>
                            <select v-model="form.category_id" class="w-full bg-base-200 border-0 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all">
                                <option :value="null">Uncategorized</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name[form.language] || Object.values(category.name)[0] }}
                                </option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-base-content/70 mb-1.5 uppercase tracking-wider">Language</label>
                            <select v-model="form.language" class="w-full bg-base-200 border-0 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all">
                                <option v-for="(label, code) in locales" :key="code" :value="code">{{ label }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-base-content/70 mb-1.5 uppercase tracking-wider">Tags (IDs)</label>
                            <!-- In a real app, this would be a multi-select component. Using a simple text input mapped to array for MVP -->
                            <input type="text" placeholder="e.g. 1, 2, 3" 
                                   @input="e => form.tag_ids = e.target.value.split(',').map(v => parseInt(v.trim())).filter(v => !isNaN(v))"
                                   class="w-full bg-base-200 border-0 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:bg-base-100 transition-all" />
                            <p class="text-[10px] text-base-content/50 mt-1">Available Tags: <span v-for="t in tags" :key="t.id" class="mr-1">{{t.name[form.language] || Object.values(t.name)[0]}}({{t.id}})</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, useForm, useHttp } from '@inertiajs/vue3';
import Layout from '../Layout.vue';

defineOptions({ layout: Layout });

// Import md-editor-v3
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import type { Category, Tag } from '../../types';

type AttachmentResponse = {
    id: number;
    url: string;
};

const props = defineProps<{
    categories: Category[];
    tags: Tag[];
    locales: Record<string, string>;
}>();

const form = useForm({
    title: '',
    summary: '',
    content: '',
    category_id: null as number | null,
    tag_ids: [] as number[],
    language: 'en',
    cover_image_id: null as number | null,
    status: 'draft',
});

const coverImageUrl = ref<string | null>(null);
const uploadingCover = ref(false);
const uploadHttp = useHttp({
    file: null as File | null,
});

// Simple dark mode detection for the editor theme
const editorTheme = computed(() => {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
});

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
        alert('Failed to upload cover image. Please check max upload size.');
    } finally {
        uploadingCover.value = false;
        target.value = ''; // reset input
    }
};

const removeCover = () => {
    form.cover_image_id = null;
    coverImageUrl.value = null;
};

// Handle Markdown image uploads
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

    const attachment = await uploadHttp.post('/api/blog/attachments') as AttachmentResponse;
    uploadHttp.file = null;

    return attachment;
};

const submit = (status: string) => {
    form.status = status; // Make sure the selected status is assigned
    form.post('/admin/blog/posts', {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by backend redirect
        }
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
