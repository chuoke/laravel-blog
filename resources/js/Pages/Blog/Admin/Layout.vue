<template>
    <div class="min-h-screen bg-base-200 text-base-content font-sans flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-base-100 border-r border-base-300 flex-shrink-0 flex flex-col hidden md:flex">
            <div class="h-16 flex items-center px-6 border-b border-base-300">
                <div class="text-xl font-extrabold flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-primary text-primary-content flex items-center justify-center font-bold text-lg shadow-sm">B</span>
                    Blog Admin
                </div>
            </div>
            
            <div class="p-4 flex-1 overflow-y-auto">
                <nav class="space-y-1">
                    <Link href="/admin/blog" :class="['flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors', $page.url === '/admin/blog' || $page.url === '/admin/blog/' ? 'bg-primary/10 text-primary' : 'text-base-content/70 hover:bg-base-200 hover:text-base-content']">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </Link>
                    
                    <Link href="/admin/blog/posts" :class="['flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors', $page.url.startsWith('/admin/blog/posts') ? 'bg-primary/10 text-primary' : 'text-base-content/70 hover:bg-base-200 hover:text-base-content']">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15"></path></svg>
                        Posts
                    </Link>
                    
                    <Link href="/admin/blog/categories" :class="['flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors', $page.url.startsWith('/admin/blog/categories') ? 'bg-primary/10 text-primary' : 'text-base-content/70 hover:bg-base-200 hover:text-base-content']">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Categories
                    </Link>
                    
                    <Link href="/admin/blog/tags" :class="['flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors', $page.url.startsWith('/admin/blog/tags') ? 'bg-primary/10 text-primary' : 'text-base-content/70 hover:bg-base-200 hover:text-base-content']">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Tags
                    </Link>
                </nav>
            </div>
            
            <div class="p-4 border-t border-base-300">
                <Link href="/" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-base-content/70 hover:bg-base-200 hover:text-base-content transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Back to App
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0">
            <!-- Mobile Header -->
            <div class="h-16 bg-base-100 border-b border-base-300 flex items-center px-4 md:hidden">
                <div class="text-lg font-extrabold flex items-center gap-2">
                    <span class="w-6 h-6 rounded bg-primary text-primary-content flex items-center justify-center font-bold text-sm shadow-sm">B</span>
                    Blog Admin
                </div>
            </div>
            
            <!-- Page Content -->
            <div class="flex-1 overflow-y-auto">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>
