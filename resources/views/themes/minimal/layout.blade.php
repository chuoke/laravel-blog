<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="{{ config('blog.color_theme', 'light') }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $seo = app(\Chuoke\Blog\Support\SeoMeta::class)->metadata(
            trim(View::yieldContent('title')),
            trim(View::yieldContent('meta_description')),
            trim(View::yieldContent('seo_default')) === '1',
        );
        $canonical = trim(View::yieldContent('canonical')) ?: $seo['canonical'];
        $robots = trim(View::yieldContent('robots')) ?: $seo['robots'];
    @endphp
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <link rel="canonical" href="{{ $canonical }}">
    @if($robots)<meta name="robots" content="{{ $robots }}">@endif
    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ $canonical }}">
    @hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif

    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/dist/themes.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,600;0,700;0,800;1,400&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-serif { font-family: 'Newsreader', Georgia, serif; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
    @stack('styles')
</head>
<body class="bg-base-100 text-base-content flex flex-col min-h-screen antialiased">

    <header class="border-b border-base-300">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
            <a href="{{ route('blog.home') }}" class="font-serif font-extrabold text-xl">{{ config('app.name', 'Blog') }}</a>
            <nav class="flex items-center gap-5 text-sm text-base-content/60">
                <a href="{{ route('blog.home') }}" class="hover:text-primary transition-colors">{{ __('blog::ui.home') }}</a>
                <a href="{{ route('blog.posts.index') }}" class="hover:text-primary transition-colors">{{ __('blog::ui.archive') }}</a>
                <form action="{{ route('blog.posts.index') }}" method="GET" class="hidden sm:block">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('blog::ui.search') }}"
                        class="text-sm border-b border-base-300 bg-transparent focus:outline-none focus:border-primary w-28 focus:w-40 transition-all duration-300 py-1 placeholder-base-content/40">
                </form>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-base-300 mt-auto">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 text-center">
            <p class="text-xs text-base-content/40">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
