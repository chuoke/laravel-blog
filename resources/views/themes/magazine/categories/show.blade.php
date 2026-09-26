@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $posts = Blog::paginatedPosts(['category_id' => $category->id], 12);
@endphp

@section('title', ($category->name[$locale] ?? array_values($category->name)[0] ?? __('blog::ui.fallback_category')))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    @include('blog::partials.masthead')
    <div class="mt-8 flex flex-col lg:flex-row gap-10">
    <div class="flex-1 min-w-0">
        <div class="mb-6 border-b-2 border-primary pb-2">
            <p class="text-[10px] font-bold uppercase tracking-widest text-primary">{{ __('blog::ui.fallback_category') }}</p>
            <h1 class="text-2xl font-extrabold uppercase tracking-tight">{{ $category->name[$locale] ?? array_values($category->name)[0] }}</h1>
        </div>
        <div class="divide-y divide-base-content/20">
            @forelse($posts as $post)
                @include('blog::partials.post-card', ['post' => $post])
            @empty
                <p class="col-span-3 text-base-content/40 text-center py-16">{{ __('blog::ui.no_posts_category') }}</p>
            @endforelse
        </div>
        @if($posts->hasPages())
            <div class="mt-10">{{ $posts->withQueryString()->links() }}</div>
        @endif
    </div>
    <aside class="w-full lg:w-80 flex-shrink-0 space-y-8">
        @include('blog::partials.sidebar')
    </aside>
</div>
</div>
@endsection
