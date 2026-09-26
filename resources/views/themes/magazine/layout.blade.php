<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="{{ config('blog.color_theme', 'light') }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $__blogTitle = trim(View::yieldContent('title'));
        $pageTitle = $__blogTitle !== '' ? $__blogTitle.' — '.config('app.name', 'Blog') : config('app.name', 'Blog');
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="@yield('meta_description', '')">
    <link rel="canonical" href="@yield('canonical', request()->url())">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="@yield('meta_description', '')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ request()->url() }}">
    @hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif

    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/dist/themes.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Source+Serif+4:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', system-ui, sans-serif; }
        .font-serif { font-family: 'Source Serif 4', Georgia, serif; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
    @stack('styles')
</head>
<body class="bg-base-200 text-base-content flex flex-col min-h-screen antialiased">

    <header class="bg-base-100 border-b-4 border-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            {{-- Top bar --}}
            <div class="flex items-center justify-between h-10 text-xs text-base-content/50 border-b border-base-200">
                <span>{{ Blog::formatDate(now(), 'full') }}</span>
                <form action="{{ route('blog.posts.index') }}" method="GET" class="flex">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('blog::ui.search') }}"
                        class="bg-transparent text-xs border-none focus:outline-none w-32 placeholder-base-content/30">
                    <button type="submit" class="text-base-content/40 hover:text-primary">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
            </div>
            {{-- Brand --}}
            <div class="py-5 text-center">
                <a href="{{ route('blog.home') }}" class="font-black text-4xl tracking-tight uppercase">{{ config('app.name', 'Blog') }}</a>
            </div>
            {{-- Nav --}}
            <nav class="flex items-center justify-center gap-8 py-3 border-t border-base-200 text-sm font-semibold uppercase tracking-wider text-base-content/70">
                <a href="{{ route('blog.home') }}" class="hover:text-primary transition-colors">{{ __('blog::ui.home') }}</a>
                <a href="{{ route('blog.posts.index') }}" class="hover:text-primary transition-colors">{{ __('blog::ui.all_posts') }}</a>
                @foreach(\Chuoke\Blog\Facades\Blog::categories()->take(5) as $navCat)
                    <a href="{{ route('blog.category.show', $navCat->slug) }}" class="hidden md:block hover:text-primary transition-colors">
                        {{ $navCat->name[app()->getLocale()] ?? array_values($navCat->name)[0] ?? '' }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-neutral text-neutral-content mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 text-center">
            <p class="text-sm opacity-60">{{ __('blog::ui.copyright', ['year' => date('Y'), 'site' => config('app.name')]) }}</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
