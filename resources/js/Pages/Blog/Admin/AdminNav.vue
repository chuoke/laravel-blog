<template>
    <div class="flex w-full h-full flex-col">
        <!-- Brand -->
        <div class="h-16 flex items-center px-6 border-b border-base-300 shrink-0">
            <div class="text-xl font-extrabold flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-primary text-primary-content flex items-center justify-center font-bold text-lg shadow-sm">B</span>
                {{ t('blogAdmin.app.name') }}
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-4">
            <Link
                v-for="item in navItems"
                :key="item.href"
                :href="item.href"
                @click="emit('navigate')"
                :class="['flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors', isActive(item) ? 'bg-primary/10 text-primary' : 'text-base-content/70 hover:bg-base-200 hover:text-base-content']"
            >
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path></svg>
                {{ item.label }}
            </Link>
        </nav>

        <div class="p-4 border-t border-base-300 shrink-0">
            <Link href="/" @click="emit('navigate')" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-base-content/70 hover:bg-base-200 hover:text-base-content transition-colors">
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                {{ t('blogAdmin.app.backToApp') }}
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useBlogRoutes } from '../admin-routes';

const emit = defineEmits<{ (e: 'navigate'): void }>();

const page = usePage();
const { t } = useI18n();
const { adminUrl } = useBlogRoutes();

const navItems = [
    {
        href: adminUrl(),
        label: t('blogAdmin.navigation.dashboard'),
        exact: true,
        icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
    },
    {
        href: adminUrl('posts'),
        label: t('blogAdmin.navigation.posts'),
        exact: false,
        icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15',
    },
    {
        href: adminUrl('categories'),
        label: t('blogAdmin.navigation.categories'),
        exact: false,
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    },
    {
        href: adminUrl('tags'),
        label: t('blogAdmin.navigation.tags'),
        exact: false,
        icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
    },
];

const isActive = (item: { href: string; exact: boolean }) =>
    item.exact
        ? page.url === item.href || page.url === item.href + '/'
        : page.url.startsWith(item.href);
</script>
