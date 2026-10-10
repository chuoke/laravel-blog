@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $posts = Blog::paginatedPosts(['tag_id' => $tag->id], 15);
@endphp

@section('title', '#' . ($tag->name[$locale] ?? array_values($tag->name)[0] ?? __('blog::ui.fallback_tag')))

@section('seo_default', '1')

@section('content')
<div class="max-w-6xl mx-auto px-5 sm:px-8 py-12 sm:py-16 flex flex-col lg:flex-row gap-14">
    <div class="flex-1 min-w-0">
        <div class="mb-10">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-secondary mb-2">{{ __('blog::ui.fallback_tag') }}</p>
            <h1 class="font-display font-extrabold text-3xl tracking-[-0.015em] text-balance">#{{ $tag->name[$locale] ?? array_values($tag->name)[0] }}</h1>
        </div>

        <div class="grid sm:grid-cols-2 gap-x-6 gap-y-10">
            @forelse($posts as $post)
                <div class="animate-fade-up" style="animation-delay: {{ $loop->index * 60 }}ms">
                    @include('blog::partials.post-card', ['post' => $post])
                </div>
            @empty
                <p class="text-base-content/40 py-10 col-span-2 text-center font-medium">{{ __('blog::ui.no_posts_tag') }}</p>
            @endforelse
        </div>

        @if($posts->hasPages())
            <div class="mt-12">{{ $posts->withQueryString()->links() }}</div>
        @endif
    </div>

    <aside class="w-full lg:w-72 flex-shrink-0 space-y-8">
        @include('blog::partials.sidebar')
    </aside>
</div>
@endsection
