@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $previous = Blog::previousPost($post);
    $next = Blog::nextPost($post);
    $related = Blog::relatedPosts($post, 4);
    $renderedPost = app(\Chuoke\Blog\Actions\RenderMarkdownWithToc::class)->execute($post->content);
@endphp

@section('title', $post->title)
@section('meta_description', $post->summary ?? Str::limit(strip_tags($post->content), 155))
@section('og_type', 'article')
@if($post->coverImage) @section('og_image', $post->coverImage->url) @endif

@section('content')
<div class="mx-auto max-w-7xl px-4 pt-8 sm:px-6">
    @include('blog::partials.masthead')
</div>

{{-- Full-width cover --}}
@if($post->coverImage)
    <div class="w-full h-64 md:h-96 overflow-hidden relative">
        <img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
    </div>
@endif

<div class="max-w-4xl mx-auto px-4 sm:px-6 {{ $post->coverImage ? '-mt-16' : 'mt-8' }} relative z-10">
    <article class="bg-base-100 border border-base-300 p-6 sm:p-10">
        <div class="flex flex-wrap items-center gap-3 text-xs mb-4">
            @if($post->category)
                <a href="{{ route('blog.category.show', $post->category->slug) }}" class="badge badge-primary badge-sm font-bold uppercase">
                    {{ $post->category->name[$locale] ?? array_values($post->category->name)[0] ?? '' }}
                </a>
            @endif
            <span class="text-base-content/50">{{ Blog::formatDate($post->published_at, 'long', $post->language) }}</span>
            <span class="text-base-content/30">|</span>
            <span class="text-base-content/50">{{ __('blog::ui.views', ['count' => number_format($post->view_count)]) }}</span>
        </div>

        <h1 class="font-serif text-3xl sm:text-4xl font-bold leading-tight mb-8">{{ $post->title }}</h1>

        @if(count($renderedPost->tableOfContents) >= 2)
            <nav aria-label="Table of contents" class="mb-10 border-y-2 border-base-content py-4">
                <div class="flex items-baseline justify-between gap-4">
                    <p class="font-serif text-lg font-black tracking-[-0.02em]">{{ __('blog::ui.in_this_article') }}</p>
                    <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-base-content/45">{{ __('blog::ui.contents') }}</span>
                </div>
                <ol class="mt-4 grid gap-x-6 gap-y-2 sm:grid-cols-2">
                    @foreach($renderedPost->tableOfContents as $item)
                        <li @class(['sm:pl-4' => $item['level'] === 3])>
                            <a href="#{{ $item['id'] }}" class="font-serif text-sm font-bold leading-snug text-base-content/70 transition-colors hover:text-primary">{{ $item['text'] }}</a>
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <div class="prose prose-lg max-w-none
            prose-headings:font-serif prose-headings:font-bold
            prose-p:text-base-content/80 prose-p:leading-[1.8]
            prose-a:text-primary prose-a:underline prose-a:decoration-primary/30 hover:prose-a:decoration-primary
            prose-blockquote:border-primary prose-blockquote:font-serif prose-blockquote:italic
            prose-img:w-full
            prose-pre:bg-neutral prose-pre:text-neutral-content prose-pre:rounded-none
            prose-code:text-sm prose-code:bg-base-200 prose-code:px-1 prose-code:py-0.5 prose-code:before:content-none prose-code:after:content-none">
            {!! $renderedPost->html !!}
        </div>

        @if($post->tags->isNotEmpty())
            <div class="flex flex-wrap gap-2 mt-10 pt-6 border-t border-base-200">
                @foreach($post->tags as $t)
                    <a href="{{ route('blog.tag.show', $t->slug) }}" class="text-xs font-bold text-base-content/60 border border-base-300 hover:border-primary hover:text-primary px-3 py-1 transition-colors uppercase tracking-wider">
                        {{ $t->name[$locale] ?? array_values($t->name)[0] ?? '' }}
                    </a>
                @endforeach
            </div>
        @endif
    </article>

    {{-- Prev / Next --}}
    <div class="grid grid-cols-2 gap-px bg-base-300 mt-px">
        <div class="bg-base-100 p-5">
            @if($previous)
                <a href="{{ route('blog.posts.show', $previous) }}" class="group block">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-base-content/40 mb-1">{{ __('blog::ui.previous') }}</p>
                    <p class="font-serif font-bold text-sm group-hover:text-primary transition-colors line-clamp-2">{{ $previous->title }}</p>
                </a>
            @endif
        </div>
        <div class="bg-base-100 p-5 text-right">
            @if($next)
                <a href="{{ route('blog.posts.show', $next) }}" class="group block">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-base-content/40 mb-1">{{ __('blog::ui.next') }}</p>
                    <p class="font-serif font-bold text-sm group-hover:text-primary transition-colors line-clamp-2">{{ $next->title }}</p>
                </a>
            @endif
        </div>
    </div>

    {{-- Related --}}
    @if($related->isNotEmpty())
    <section class="mt-10 mb-10">
        <div class="flex items-center gap-3 mb-6 border-b-2 border-primary pb-2">
            <h2 class="text-lg font-extrabold uppercase tracking-wider">{{ __('blog::ui.you_may_also_like') }}</h2>
        </div>
        <div class="divide-y divide-base-content/20">
            @foreach($related as $rel)
                @include('blog::partials.post-card', ['post' => $rel])
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
