@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $categoryName = $category->name[$locale] ?? array_values($category->name)[0] ?? __('blog::ui.fallback_category');
    $posts = Blog::paginatedPosts(['category_id' => $category->id], 12);
    $popular = Blog::popularPosts(4);
@endphp
@section('title', $category->name[$locale] ?? array_values($category->name)[0] ?? __('blog::ui.fallback_category'))
@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    <header class="border-b border-base-content/15 py-10 sm:py-12">
        <p class="text-[10px] font-black uppercase tracking-[.16em] text-primary">{{ __('blog::ui.browse_section') }}</p>
        <h1 class="mt-2 text-4xl font-black tracking-[-.055em] sm:text-5xl">{{ $categoryName }}</h1>
        <p class="mt-4 max-w-xl text-sm leading-relaxed text-base-content/70">{{ __('blog::ui.category_intro', ['category' => $categoryName]) }}</p>
    </header>

    <div class="grid lg:grid-cols-[minmax(0,1fr)_17rem] lg:gap-10">
        <main class="py-8">
            <div class="flex items-end justify-between border-b-2 border-base-content pb-3">
                <h2 class="text-xl font-black tracking-[-.035em]">{{ __('blog::ui.latest_posts') }}</h2>
                <span class="text-[9px] font-bold uppercase tracking-[.14em] text-base-content/60">{{ __('blog::ui.newest_first') }}</span>
            </div>

            <div class="divide-y divide-base-content/15">
                @forelse($posts as $post)
                    <article class="group relative grid gap-4 py-5 sm:grid-cols-[minmax(0,1fr)_9rem] sm:items-center sm:pl-16">
                        <time datetime="{{ $post->published_at?->toDateString() }}" class="absolute left-0 top-5 text-[10px] font-bold uppercase leading-tight text-base-content/60 max-sm:hidden">
                            {{ Blog::formatDate($post->published_at, 'monthName', $post->language) }}<br>{{ Blog::formatDate($post->published_at, 'day', $post->language) }}<br><span class="text-[8px]">{{ Blog::formatDate($post->published_at, 'year', $post->language) }}</span>
                        </time>
                        <a href="{{ route('blog.posts.show', $post) }}" class="aspect-[16/10] overflow-hidden bg-base-200 sm:col-start-2 sm:row-start-1">
                            @if($post->coverImage)<img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" loading="lazy" width="288" height="180" class="h-full w-full object-cover transition-transform duration-500 [@media(hover:hover)]:group-hover:scale-105">@endif
                        </a>
                        <a href="{{ route('blog.posts.show', $post) }}" class="sm:col-start-1 sm:row-start-1">
                            <p class="text-[9px] font-bold uppercase tracking-[.12em] text-primary">{{ $categoryName }} <span class="mx-1 text-base-content/25">/</span> {{ Blog::formatDate($post->published_at, 'short', $post->language) }}</p>
                            <h3 class="mt-1 text-xl font-black leading-snug tracking-[-.025em] transition-colors [@media(hover:hover)]:group-hover:text-primary lg:text-2xl">{{ $post->title }}</h3>
                            @if($post->summary)<p class="mt-2 line-clamp-2 text-xs leading-relaxed text-base-content/70">{{ $post->summary }}</p>@endif
                        </a>
                    </article>
                @empty
                    <p class="py-12 text-sm text-base-content/60">{{ __('blog::ui.no_stories_section') }}</p>
                @endforelse
            </div>

            @if($posts->hasPages())
                <div class="mt-8">{{ $posts->withQueryString()->links() }}</div>
            @endif
        </main>

        <aside class="border-base-content/15 py-8 max-lg:border-t lg:border-l lg:pl-7">
            <section>
                <p class="text-[10px] font-black uppercase tracking-[.14em] text-primary">{{ __('blog::ui.most_read') }}</p>
                <ol class="mt-3 divide-y divide-base-content/15">
                    @foreach($popular as $index => $post)
                        <li><a href="{{ route('blog.posts.show', $post) }}" class="group flex gap-3 py-4"><span class="text-xl font-light tabular-nums text-base-content/65">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span><span class="text-xs font-bold leading-snug transition-colors [@media(hover:hover)]:group-hover:text-primary">{{ $post->title }}</span></a></li>
                    @endforeach
                </ol>
            </section>
            <section class="mt-8 border-t border-base-content/15 pt-5">
                <p class="text-[10px] font-black uppercase tracking-[.14em] text-primary">{{ __('blog::ui.stay_current') }}</p>
                <h2 class="mt-2 text-base font-black tracking-[-.025em]">{{ __('blog::ui.weekly_briefing') }}</h2>
                <p class="mt-2 text-xs leading-relaxed text-base-content/70">{{ __('blog::ui.weekly_briefing_description', ['site' => config('app.name')]) }}</p>
                <a href="{{ route('blog.posts.index') }}" class="mt-4 inline-flex border border-primary px-3 py-2 text-[10px] font-black uppercase tracking-[.1em] text-primary transition-colors hover:bg-primary hover:text-primary-content">{{ __('blog::ui.browse_all_stories') }}</a>
            </section>
        </aside>
    </div>
</div>
@endsection
