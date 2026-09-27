<template>
    <div class="h-dvh bg-base-200 text-base-content font-sans flex overflow-hidden">
        <!-- Desktop Sidebar -->
        <aside class="hidden md:flex w-64 bg-base-100 border-r border-base-300 shrink-0">
            <AdminNav />
        </aside>

        <!-- Mobile Navigation Drawer -->
        <Transition name="drawer-fade">
            <div v-if="navOpen" class="fixed inset-0 z-40 bg-base-content/40 backdrop-blur-sm md:hidden" @click="navOpen = false"></div>
        </Transition>
        <Transition name="drawer-slide">
            <aside v-if="navOpen" class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-base-100 border-r border-base-300 shadow-2xl md:hidden">
                <AdminNav @navigate="navOpen = false" />
            </aside>
        </Transition>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0">
            <!-- Mobile Header -->
            <div class="h-16 bg-base-100 border-b border-base-300 flex items-center px-4 shrink-0 md:hidden">
                <button type="button" @click="navOpen = true" class="p-2 -ml-2 mr-2 rounded-lg text-base-content/70 hover:bg-base-200 hover:text-base-content transition-colors" :aria-label="t('blogAdmin.openNavigation')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="text-lg font-extrabold flex items-center gap-2">
                    <span class="w-6 h-6 rounded bg-primary text-primary-content flex items-center justify-center font-bold text-sm shadow-sm">B</span>
                    {{ t('blogAdmin.name') }}
                </div>
            </div>

            <!-- Page Content -->
            <div class="flex-1 overflow-y-auto" scroll-region>
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminNav from './AdminNav.vue';

const navOpen = ref(false);
const page = usePage();
const { t } = useI18n();

// Close the drawer on any navigation (including browser back/forward)
watch(() => page.url, () => {
    navOpen.value = false;
});

const onKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') navOpen.value = false;
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>

<style>
.drawer-fade-enter-active,
.drawer-fade-leave-active {
    transition: opacity 0.2s ease;
}
.drawer-fade-enter-from,
.drawer-fade-leave-to {
    opacity: 0;
}
.drawer-slide-enter-active,
.drawer-slide-leave-active {
    transition: transform 0.25s ease;
}
.drawer-slide-enter-from,
.drawer-slide-leave-to {
    transform: translateX(-100%);
}
</style>
