@extends(config('blog.layout') ?? 'blog::layout')
@php
    $posts = Blog::paginatedPosts(['search' => request('search')], 12);
@endphp

@section('title', request('search') ? __('blog::ui.search_title', ['search' => request('search')]) : __('blog::ui.all_posts'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    @include('blog::partials.masthead')
    <div class="mt-8 flex flex-col lg:flex-row gap-10">
    <div class="flex-1 min-w-0">
        @if(request('search'))
            <div class="mb-6 border-b-2 border-primary pb-2">
                <p class="text-xs font-bold uppercase tracking-widest text-base-content/40">{{ __('blog::ui.results_for') }}</p>
                <h1 class="text-2xl font-extrabold uppercase tracking-tight">"{{ request('search') }}"</h1>
            </div>
        @else
            <div class="mb-6 border-b-2 border-primary pb-2">
                <h1 class="text-lg font-extrabold uppercase tracking-wider">{{ __('blog::ui.all_posts') }}</h1>
            </div>
        @endif

        <div class="divide-y divide-base-content/20">
            @forelse($posts as $post)
                @include('blog::partials.post-card', ['post' => $post])
            @empty
                <div class="col-span-3 text-center py-20">
                    <p class="text-base-content/40 font-semibold">{{ __('blog::ui.no_posts_found') }}</p>
                </div>
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
