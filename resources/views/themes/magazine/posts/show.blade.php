@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $previous = Blog::previousPost($post);
    $next = Blog::nextPost($post);
    $related = Blog::relatedPosts($post, 4);
@endphp

@section('title', $post->title . ' — ' . config('app.name'))
@section('meta_description', $post->summary ?? Str::limit(strip_tags($post->content), 155))
@section('og_type', 'article')
@if($post->coverImage) @section('og_image', $post->coverImage->url) @endif

@section('content')
{{-- Full-width cover --}}
@if($post->coverImage)
    <div class="w-full h-64 md:h-96 overflow-hidden relative">
        <img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
    </div>
@endif

<div class="max-w-4xl mx-auto px-4 sm:px-6 -mt-16 relative z-10">
    <article class="bg-base-100 border border-base-300 p-6 sm:p-10">
        <div class="flex flex-wrap items-center gap-3 text-xs mb-4">
            @if($post->category)
                <a href="{{ route('blog.category.show', $post->category->slug) }}" class="badge badge-primary badge-sm font-bold uppercase">
                    {{ $post->category->name[$locale] ?? array_values($post->category->name)[0] ?? '' }}
                </a>
            @endif
            <span class="text-base-content/50">{{ $post->published_at?->format('F d, Y') }}</span>
            <span class="text-base-content/30">|</span>
            <span class="text-base-content/50">{{ number_format($post->view_count) }} views</span>
        </div>

        <h1 class="font-serif text-3xl sm:text-4xl font-bold leading-tight mb-8">{{ $post->title }}</h1>

        <div class="prose prose-lg max-w-none
            prose-headings:font-serif prose-headings:font-bold
            prose-p:text-base-content/80 prose-p:leading-[1.8]
            prose-a:text-primary prose-a:underline prose-a:decoration-primary/30 hover:prose-a:decoration-primary
            prose-blockquote:border-primary prose-blockquote:font-serif prose-blockquote:italic
            prose-img:w-full
            prose-pre:bg-neutral prose-pre:text-neutral-content prose-pre:rounded-none
            prose-code:text-sm prose-code:bg-base-200 prose-code:px-1 prose-code:py-0.5 prose-code:before:content-none prose-code:after:content-none">
            {!! app(\Chuoke\Blog\Actions\RenderMarkdown::class)->execute($post->content) !!}
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
                <a href="{{ route('blog.posts.show', $previous->slug) }}" class="group block">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-base-content/40 mb-1">&larr; Previous</p>
                    <p class="font-serif font-bold text-sm group-hover:text-primary transition-colors line-clamp-2">{{ $previous->title }}</p>
                </a>
            @endif
        </div>
        <div class="bg-base-100 p-5 text-right">
            @if($next)
                <a href="{{ route('blog.posts.show', $next->slug) }}" class="group block">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-base-content/40 mb-1">Next &rarr;</p>
                    <p class="font-serif font-bold text-sm group-hover:text-primary transition-colors line-clamp-2">{{ $next->title }}</p>
                </a>
            @endif
        </div>
    </div>

    {{-- Related --}}
    @if($related->isNotEmpty())
    <section class="mt-10 mb-10">
        <div class="flex items-center gap-3 mb-6 border-b-2 border-primary pb-2">
            <h2 class="text-lg font-extrabold uppercase tracking-wider">You May Also Like</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($related as $rel)
                @include('blog::partials.post-card', ['post' => $rel])
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
