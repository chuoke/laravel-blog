<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="{{ config('blog.color_theme', 'editorial') }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'Blog'))</title>
    <meta name="description" content="@yield('meta_description', '')">
    <link rel="canonical" href="@yield('canonical', request()->url())">
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:description" content="@yield('meta_description', '')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ request()->url() }}">
    @hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Public+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    {{-- Precompiled Tailwind v4 + DaisyUI bundle (built via `npm run build:theme`,
         committed under resources/css/blog/). Inlined so the theme renders
         correctly with zero build step or asset publishing required. --}}
    <style>{!! \Chuoke\Blog\Support\ThemeAssets::css('default') !!}</style>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('styles')
</head>
<body class="bg-base-200 text-base-content antialiased flex flex-col min-h-screen">

    <header class="sticky top-0 z-50 bg-base-100/90 backdrop-blur border-b border-base-300/70">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 h-20 flex items-center justify-between">
            <a href="{{ route('blog.home') }}" class="font-display font-extrabold text-[1.375rem] tracking-[-0.02em] text-base-content">
                {{ config('app.name', 'Blog') }}<span class="text-primary">.</span>
            </a>

            <nav class="hidden md:flex items-center gap-9 text-[0.9rem] font-medium text-base-content/60">
                <a href="{{ route('blog.home') }}" class="hover:text-base-content transition-colors duration-150">Home</a>
                <a href="{{ route('blog.posts.index') }}" class="hover:text-base-content transition-colors duration-150">Articles</a>
            </nav>

            <div class="flex items-center gap-2">
                <form action="{{ route('blog.posts.index') }}" method="GET" class="hidden md:block">
                    <label class="relative block">
                        <span class="sr-only">Search articles</span>
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-base-content/35" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search"
                            class="w-44 focus:w-64 rounded-field bg-base-200 border border-transparent focus:border-primary/40 focus:bg-base-100 pl-9 pr-3 py-2 text-sm transition-[width,background-color,border-color] duration-300 ease-out outline-none">
                    </label>
                </form>

                <div x-data="{ open: false }" class="md:hidden relative">
                    <button @click="open = !open" type="button" aria-label="Toggle menu" aria-expanded="false" :aria-expanded="open.toString()"
                        class="relative w-10 h-10 grid place-items-center rounded-field hover:bg-base-200 active:scale-95 transition-[background-color,transform] duration-150">
                        <svg class="w-5 h-5 absolute transition-[opacity,transform] duration-150" :class="open ? 'opacity-0 scale-90' : 'opacity-100 scale-100'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg class="w-5 h-5 absolute transition-[opacity,transform] duration-150" :class="open ? 'opacity-100 scale-100' : 'opacity-0 scale-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="absolute right-0 top-12 w-52 rounded-box bg-base-100 shadow-[0_1px_2px_rgba(15,15,15,0.04),0_16px_32px_-12px_rgba(15,15,15,0.2)] border border-base-300/70 p-2" style="display: none;">
                        <a href="{{ route('blog.home') }}" class="block px-3 py-2 rounded-field text-sm font-medium hover:bg-base-200">Home</a>
                        <a href="{{ route('blog.posts.index') }}" class="block px-3 py-2 rounded-field text-sm font-medium hover:bg-base-200">Articles</a>
                        <form action="{{ route('blog.posts.index') }}" method="GET" class="px-1 pt-1">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search"
                                class="w-full rounded-field bg-base-200 px-3 py-2 text-sm outline-none">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-base-300/70 mt-20">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 py-10 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="font-display font-bold text-sm tracking-[-0.01em] text-base-content/70">
                {{ config('app.name', 'Blog') }}<span class="text-primary">.</span>
            </p>
            <p class="text-xs text-base-content/40 font-medium">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
