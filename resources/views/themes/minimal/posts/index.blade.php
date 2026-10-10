@extends(config('blog.layout') ?? 'blog::layout')
@php
    $posts = Blog::paginatedPosts(['search' => request('search')], 15);
@endphp

@section('title', request('search') ? __('blog::ui.search_title', ['search' => request('search')]) : __('blog::ui.archive'))

@section('seo_default', '1')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
    @if(request('search'))
        <div class="mb-10">
            <p class="text-xs font-semibold text-base-content/40 mb-1">{{ __('blog::ui.results_for') }}</p>
            <h1 class="font-serif text-3xl font-extrabold">"{{ request('search') }}"</h1>
        </div>
    @else
        <h1 class="font-serif text-3xl font-extrabold mb-10">{{ __('blog::ui.archive') }}</h1>
    @endif

    <div class="space-y-8">
        @forelse($posts as $post)
            @include('blog::partials.post-card', ['post' => $post])
        @empty
            <p class="text-base-content/40 text-center py-16">{{ __('blog::ui.no_posts_found') }}</p>
        @endforelse
    </div>

    @if($posts->hasPages())
        <div class="mt-12 pt-8 border-t border-base-200">{{ $posts->withQueryString()->links() }}</div>
    @endif

    <div class="mt-16">
        @include('blog::partials.sidebar')
    </div>
</div>
@endsection
