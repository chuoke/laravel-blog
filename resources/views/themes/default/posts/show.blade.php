@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $previous = Blog::previousPost($post);
    $next = Blog::nextPost($post);
    $related = Blog::relatedPosts($post, 3);
@endphp

@section('title', $post->title . ' — ' . config('app.name'))
@section('meta_description', $post->summary ?? Str::limit(strip_tags($post->content), 155))
@section('og_type', 'article')
@if($post->coverImage) @section('og_image', $post->coverImage->url) @endif

@section('content')
<div class="max-w-6xl mx-auto px-5 sm:px-8 py-12 sm:py-16 flex flex-col lg:flex-row gap-14">
    <div class="flex-1 min-w-0 max-w-3xl">
        <article class="animate-fade-up">
            <div class="flex flex-wrap items-center gap-3 text-xs mb-5">
                @if($post->category)
                    <a href="{{ route('blog.category.show', $post->category->slug) }}" class="font-bold text-primary uppercase tracking-[0.08em]">
                        {{ $post->category->name[$locale] ?? array_values($post->category->name)[0] ?? '' }}
                    </a>
                    <span class="text-base-content/25">&bull;</span>
                @endif
                <span class="text-base-content/50 font-medium">{{ $post->published_at?->format('F d, Y') }}</span>
                <span class="text-base-content/25">&bull;</span>
                <span class="text-base-content/50 font-medium tabular-nums">{{ number_format($post->view_count) }} views</span>
            </div>

            <h1 class="font-display font-extrabold text-3xl sm:text-[2.5rem] leading-[1.1] tracking-[-0.02em] text-balance mb-8">{{ $post->title }}</h1>

            @if($post->coverImage)
                <div class="aspect-[16/9] rounded-box overflow-hidden mb-10 shadow-[0_1px_2px_rgba(15,15,15,0.05),0_20px_40px_-18px_rgba(15,15,15,0.28)]">
                    <img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" class="w-full h-full object-cover outline outline-1 outline-black/10 -outline-offset-1">
                </div>
            @endif

            <div class="prose prose-lg max-w-none
                        prose-headings:font-display prose-headings:font-bold prose-headings:tracking-[-0.01em]
                        prose-p:text-base-content/80 prose-p:leading-relaxed
                        prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-a:font-medium
                        prose-strong:text-base-content prose-blockquote:border-l-primary prose-blockquote:not-italic prose-blockquote:font-normal
                        prose-img:rounded-box
                        prose-pre:bg-neutral prose-pre:text-neutral-content prose-pre:rounded-box
                        prose-code:before:content-none prose-code:after:content-none">
                {!! app(\Chuoke\Blog\Actions\RenderMarkdown::class)->execute($post->content) !!}
            </div>

            @if($post->tags->isNotEmpty())
                <div class="flex flex-wrap gap-2 mt-10 pt-8 border-t border-base-300">
                    @foreach($post->tags as $t)
                        <a href="{{ route('blog.tag.show', $t->slug) }}" class="text-xs font-semibold text-base-content/65 bg-base-200 hover:bg-primary/10 hover:text-primary px-3 py-1.5 rounded-selector transition-colors duration-150">
                            #{{ $t->name[$locale] ?? array_values($t->name)[0] ?? '' }}
                        </a>
                    @endforeach
                </div>
            @endif
        </article>

        @if($previous || $next)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-10">
            @if($previous)
                <a href="{{ route('blog.posts.show', $previous->slug) }}" class="group rounded-box bg-base-100 p-5 shadow-[0_1px_2px_rgba(15,15,15,0.04),0_8px_20px_-16px_rgba(15,15,15,0.14)] hover:shadow-[0_1px_2px_rgba(15,15,15,0.05),0_16px_32px_-16px_rgba(15,15,15,0.22)] transition-shadow duration-300">
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-base-content/40 mb-1.5">&larr; Previous</p>
                    <p class="font-display font-bold text-sm group-hover:text-primary transition-colors duration-150 line-clamp-2">{{ $previous->title }}</p>
                </a>
            @else <div></div> @endif
            @if($next)
                <a href="{{ route('blog.posts.show', $next->slug) }}" class="group rounded-box bg-base-100 p-5 shadow-[0_1px_2px_rgba(15,15,15,0.04),0_8px_20px_-16px_rgba(15,15,15,0.14)] hover:shadow-[0_1px_2px_rgba(15,15,15,0.05),0_16px_32px_-16px_rgba(15,15,15,0.22)] transition-shadow duration-300 text-right">
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-base-content/40 mb-1.5">Next &rarr;</p>
                    <p class="font-display font-bold text-sm group-hover:text-primary transition-colors duration-150 line-clamp-2">{{ $next->title }}</p>
                </a>
            @endif
        </div>
        @endif

        @if($related->isNotEmpty())
        <section class="mt-14">
            <h2 class="font-display font-bold text-lg tracking-[-0.01em] mb-6">Related</h2>
            <div class="grid sm:grid-cols-3 gap-6">
                @foreach($related as $rel)
                    @include('blog::partials.post-card', ['post' => $rel])
                @endforeach
            </div>
        </section>
        @endif
    </div>

    <aside class="w-full lg:w-72 flex-shrink-0 space-y-8">
        @include('blog::partials.sidebar')
    </aside>
</div>
@endsection
