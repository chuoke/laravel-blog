@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $previous = Blog::previousPost($post);
    $next = Blog::nextPost($post);
    $related = Blog::relatedPosts($post, 3);
    $popular = Blog::popularPosts(4);
    $renderedPost = app(\Chuoke\Blog\Actions\RenderMarkdownWithToc::class)->execute($post->content);
@endphp
@section('title', $post->title)
@section('meta_description', $post->summary ?? Str::limit(strip_tags($post->content), 155))
@section('og_type', 'article')
@if($post->coverImage) @section('og_image', $post->coverImage->url) @endif
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14">
    <header class="max-w-4xl pb-8 sm:pb-10">
        <p class="text-[10px] font-black uppercase tracking-[.16em] text-primary">{{ $post->category?->name[$locale] ?? __('blog::ui.fallback_news') }}</p>
        <h1 class="mt-3 text-4xl font-black wrap-break-word leading-[1.05] tracking-[-.06em] text-balance sm:text-6xl">{{ $post->title }}</h1>
        @if($post->summary)<p class="mt-6 max-w-2xl text-base leading-relaxed text-base-content/70 sm:text-lg">{{ $post->summary }}</p>@endif
        <p class="mt-6 text-[10px] font-bold uppercase tracking-[.12em] text-base-content/60">{{ __('blog::ui.published', ['date' => Blog::formatDate($post->published_at, 'long', $post->language), 'count' => number_format($post->view_count)]) }}</p>
    </header>

    <div class="grid border-t border-base-content/15 lg:grid-cols-[minmax(0,1fr)_17rem] lg:gap-10">
        <article class="min-w-0 py-8">
            @if($post->coverImage)
                <figure class="aspect-[16/9] overflow-hidden bg-base-200">
                    <img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" class="h-full w-full object-cover">
                </figure>
            @endif

            <div class="prose prose-lg mt-8 max-w-3xl prose-headings:scroll-mt-24 prose-headings:font-black prose-headings:tracking-[-.035em] prose-p:leading-relaxed prose-a:text-primary prose-a:font-medium prose-img:rounded-none prose-pre:bg-neutral prose-pre:text-neutral-content">{!! $renderedPost->html !!}</div>

            <nav aria-label="Adjacent articles" class="mt-10 grid gap-5 border-t border-base-content/15 pt-6 sm:grid-cols-2">
                @if($previous)
                    <a href="{{ route('blog.posts.show', $previous) }}" class="group">
                        <p class="text-[10px] font-bold uppercase tracking-[.14em] text-base-content/60">{{ __('blog::ui.previous_story') }}</p>
                        <p class="mt-1 text-sm font-black leading-snug transition-colors group-hover:text-primary">{{ $previous->title }}</p>
                    </a>
                @endif
                @if($next)
                    <a href="{{ route('blog.posts.show', $next) }}" class="group sm:text-right">
                        <p class="text-[10px] font-bold uppercase tracking-[.14em] text-base-content/60">{{ __('blog::ui.next_story') }}</p>
                        <p class="mt-1 text-sm font-black leading-snug transition-colors group-hover:text-primary">{{ $next->title }}</p>
                    </a>
                @endif
            </nav>
        </article>

        <aside class="py-8 lg:border-l lg:pl-7">
            @if(count($renderedPost->tableOfContents) >= 2)
                <nav aria-label="Table of contents" class="lg:sticky lg:top-24">
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-primary">{{ __('blog::ui.in_this_article') }}</p>
                    <ol class="mt-3 space-y-3 border-l border-base-content/15 pl-4">
                        @foreach($renderedPost->tableOfContents as $item)
                            <li @class(['ml-3' => $item['level'] === 3])><a href="#{{ $item['id'] }}" class="text-xs font-bold leading-snug text-base-content/70 transition-colors hover:text-primary">{{ $item['text'] }}</a></li>
                        @endforeach
                    </ol>
                </nav>
            @else
                <section>
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-primary">{{ __('blog::ui.most_read') }}</p>
                    <ol class="mt-3 divide-y divide-base-content/15">
                        @foreach($popular as $index => $popularPost)
                            <li><a href="{{ route('blog.posts.show', $popularPost) }}" class="group flex gap-3 py-4"><span class="text-xl font-light tabular-nums text-base-content/65">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span><span class="text-xs font-bold leading-snug transition-colors group-hover:text-primary">{{ $popularPost->title }}</span></a></li>
                        @endforeach
                    </ol>
                </section>
            @endif
        </aside>
    </div>

    @if($related->isNotEmpty())
        <section class="mt-8 border-t-2 border-base-content pt-5">
            <div class="flex items-center justify-between"><h2 class="text-2xl font-black tracking-[-.04em]">{{ __('blog::ui.continue_reading') }}</h2><a href="{{ route('blog.posts.index') }}" class="text-xs font-bold text-primary">{{ __('blog::ui.view_all') }}</a></div>
            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($related as $relatedPost)
                    <a href="{{ route('blog.posts.show', $relatedPost) }}" class="group">
                        <div class="aspect-[16/10] overflow-hidden bg-base-200">@if($relatedPost->coverImage)<img src="{{ $relatedPost->coverImage->url }}" alt="{{ $relatedPost->title }}" loading="lazy" width="400" height="250" class="h-full w-full object-cover transition-transform duration-500 [@media(hover:hover)]:group-hover:scale-105">@endif</div>
                        <p class="mt-3 text-[9px] font-bold uppercase tracking-[.12em] text-primary">{{ $relatedPost->category?->name[$locale] ?? __('blog::ui.fallback_news') }}</p>
                        <h3 class="mt-1 text-base font-black leading-snug transition-colors group-hover:text-primary">{{ $relatedPost->title }}</h3>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
