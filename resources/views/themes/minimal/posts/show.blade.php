@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $previous = Blog::previousPost($post);
    $next = Blog::nextPost($post);
    $related = Blog::relatedPosts($post, 3);
    $renderedPost = app(\Chuoke\Blog\Actions\RenderMarkdownWithToc::class)->execute($post->content);
@endphp

@section('title', $post->title)
@section('meta_description', $post->summary ?? Str::limit(strip_tags($post->content), 155))
@section('og_type', 'article')
@if($post->coverImage) @section('og_image', $post->coverImage->url) @endif

@section('content')
<article class="max-w-3xl mx-auto px-4 sm:px-6 py-12">

    <div class="flex items-center gap-2 text-xs text-base-content/40 mb-4 font-medium">
        @if($post->category)
            <a href="{{ route('blog.category.show', $post->category->slug) }}" class="text-base-content/70 hover:text-primary transition-colors font-semibold">
                {{ $post->category->name[$locale] ?? array_values($post->category->name)[0] ?? '' }}
            </a>
            <span>&mdash;</span>
        @endif
        <time>{{ Blog::formatDate($post->published_at, 'long', $post->language) }}</time>
        <span>&bull;</span>
        <span>{{ __('blog::ui.views', ['count' => number_format($post->view_count)]) }}</span>
    </div>

    <h1 class="font-serif text-4xl sm:text-5xl font-extrabold leading-[1.15] tracking-tight mb-8">{{ $post->title }}</h1>

    @if($post->coverImage)
        <div class="rounded-lg overflow-hidden mb-10">
            <img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" class="w-full">
        </div>
    @endif

    @if(count($renderedPost->tableOfContents) >= 2)
        <nav aria-label="Table of contents" class="mb-10 border-y border-base-300 py-5">
            <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.14em] text-base-content/45">{{ __('blog::ui.contents') }}</p>
            <ol class="space-y-2">
                @foreach($renderedPost->tableOfContents as $item)
                    <li @class(['ml-4' => $item['level'] === 3])>
                        <a href="#{{ $item['id'] }}" class="text-sm text-base-content/60 transition-colors hover:text-primary">{{ $item['text'] }}</a>
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    <div class="prose prose-lg max-w-none
        prose-headings:font-serif prose-headings:font-extrabold
        prose-p:text-base-content/80 prose-p:leading-[1.8]
        prose-a:text-primary prose-a:underline prose-a:decoration-primary/30 hover:prose-a:decoration-primary
        prose-blockquote:border-base-300 prose-blockquote:text-base-content/60 prose-blockquote:font-serif prose-blockquote:italic
        prose-img:rounded-lg
        prose-pre:bg-neutral prose-pre:text-neutral-content prose-pre:rounded-lg
        prose-code:text-sm prose-code:bg-base-200 prose-code:px-1 prose-code:py-0.5 prose-code:rounded prose-code:before:content-none prose-code:after:content-none">
        {!! $renderedPost->html !!}
    </div>

    @if($post->tags->isNotEmpty())
        <div class="flex flex-wrap gap-3 mt-10 pt-8 border-t border-base-200">
            @foreach($post->tags as $t)
                <a href="{{ route('blog.tag.show', $t->slug) }}" class="text-sm text-base-content/50 hover:text-primary transition-colors">
                    #{{ $t->name[$locale] ?? array_values($t->name)[0] ?? '' }}
                </a>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-2 gap-8 mt-12 pt-8 border-t border-base-300">
        <div>
            @if($previous)
                <a href="{{ route('blog.posts.show', $previous) }}" class="group block">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-base-content/40 mb-1">{{ __('blog::ui.previous') }}</p>
                    <p class="font-serif font-bold text-sm group-hover:text-primary transition-colors line-clamp-2">{{ $previous->title }}</p>
                </a>
            @endif
        </div>
        <div class="text-right">
            @if($next)
                <a href="{{ route('blog.posts.show', $next) }}" class="group block">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-base-content/40 mb-1">{{ __('blog::ui.next') }}</p>
                    <p class="font-serif font-bold text-sm group-hover:text-primary transition-colors line-clamp-2">{{ $next->title }}</p>
                </a>
            @endif
        </div>
    </div>

    @if($related->isNotEmpty())
    <section class="mt-14 pt-8 border-t border-base-300">
        <h2 class="font-serif text-xl font-bold mb-6">{{ __('blog::ui.you_might_also_like') }}</h2>
        <div class="space-y-6">
            @foreach($related as $rel)
                <a href="{{ route('blog.posts.show', $rel) }}" class="group flex items-start gap-4">
                    @if($rel->coverImage)
                        <div class="w-20 h-14 rounded overflow-hidden flex-shrink-0">
                            <img src="{{ $rel->coverImage->url }}" alt="{{ $rel->title }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <div>
                        <p class="font-serif font-bold text-sm group-hover:text-primary transition-colors line-clamp-2 leading-snug">{{ $rel->title }}</p>
                        <p class="text-xs text-base-content/40 mt-1">{{ Blog::formatRelativeDate($rel->published_at, $rel->language) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif
</article>
@endsection
