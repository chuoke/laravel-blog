@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $posts = Blog::paginatedPosts(['category_id' => $category->id], 15);
@endphp

@section('title', ($category->name[$locale] ?? array_values($category->name)[0] ?? __('blog::ui.fallback_category')))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
    <div class="mb-10">
        <p class="text-xs font-semibold text-primary mb-1">{{ __('blog::ui.fallback_category') }}</p>
        <h1 class="font-serif text-3xl font-extrabold">{{ $category->name[$locale] ?? array_values($category->name)[0] }}</h1>
        @if($desc = ($category->description[$locale] ?? array_values($category->description ?? [])[0] ?? null))
            <p class="text-base-content/60 mt-2 text-sm leading-relaxed">{{ $desc }}</p>
        @endif
    </div>

    <div class="space-y-8">
        @forelse($posts as $post)
            @include('blog::partials.post-card', ['post' => $post])
        @empty
            <p class="text-base-content/40 text-center py-16">{{ __('blog::ui.no_posts_category') }}</p>
        @endforelse
    </div>

    @if($posts->hasPages())
        <div class="mt-12 pt-8 border-t border-base-200">{{ $posts->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
