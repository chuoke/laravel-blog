@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $posts = Blog::paginatedPosts(['tag_id' => $tag->id], 15);
@endphp

@section('title', '#' . ($tag->name[$locale] ?? array_values($tag->name)[0] ?? __('blog::ui.fallback_tag')))

@section('seo_default', '1')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
    <div class="mb-10">
        <p class="text-xs font-semibold text-secondary mb-1">{{ __('blog::ui.fallback_tag') }}</p>
        <h1 class="font-serif text-3xl font-extrabold">#{{ $tag->name[$locale] ?? array_values($tag->name)[0] }}</h1>
    </div>

    <div class="space-y-8">
        @forelse($posts as $post)
            @include('blog::partials.post-card', ['post' => $post])
        @empty
            <p class="text-base-content/40 text-center py-16">{{ __('blog::ui.no_posts_tag') }}</p>
        @endforelse
    </div>

    @if($posts->hasPages())
        <div class="mt-12 pt-8 border-t border-base-200">{{ $posts->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
