@extends(config('blog.layout') ?? 'blog::layout')
@php
    $posts = Blog::paginatedPosts(['search' => request('search')], 12);
@endphp

@section('title', request('search') ? __('blog::ui.search_title', ['search' => request('search')]) : __('blog::ui.articles'))

@section('seo_default', '1')

@section('content')
<div class="max-w-6xl mx-auto px-5 sm:px-8 py-12 sm:py-16 flex flex-col lg:flex-row gap-14">
    <div class="flex-1 min-w-0">
        <div class="mb-10">
            @if(request('search'))
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-secondary mb-2">{{ __('blog::ui.results_for') }}</p>
                <h1 class="font-display font-extrabold text-3xl tracking-[-0.015em] text-balance">&ldquo;{{ request('search') }}&rdquo;</h1>
            @else
                <h1 class="font-display font-extrabold text-3xl tracking-[-0.015em]">{{ __('blog::ui.articles') }}</h1>
            @endif
        </div>

        <div class="grid sm:grid-cols-2 gap-x-6 gap-y-10">
            @forelse($posts as $post)
                <div class="animate-fade-up" style="animation-delay: {{ $loop->index * 60 }}ms">
                    @include('blog::partials.post-card', ['post' => $post])
                </div>
            @empty
                <div class="col-span-2 text-center py-20">
                    <div class="w-14 h-14 bg-base-300 rounded-box flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-base-content/25" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15"></path></svg>
                    </div>
                    <p class="text-base-content/40 font-semibold">{{ __('blog::ui.no_posts_found') }}</p>
                </div>
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
