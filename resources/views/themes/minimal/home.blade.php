@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $pinnedPosts = Blog::pinnedPosts(1);
    $latestPosts = Blog::latestPosts(8);
@endphp

@section('title', config('app.name'))

@section('seo_default', '1')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">

    @if($pinnedPosts->isNotEmpty())
    <section class="mb-16">
        @foreach($pinnedPosts as $pinned)
            <a href="{{ route('blog.posts.show', $pinned) }}" class="group block">
                @if($pinned->coverImage)
                    <div class="aspect-[2/1] rounded-lg overflow-hidden mb-6">
                        <img src="{{ $pinned->coverImage->url }}" alt="{{ $pinned->title }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-700">
                    </div>
                @endif
                <div class="flex items-center gap-2 text-xs text-base-content/40 mb-3 font-medium">
                    <span class="text-accent font-semibold">{{ __('blog::ui.featured') }}</span>
                    <span>&mdash;</span>
                    <span>{{ Blog::formatDate($pinned->published_at, 'short', $pinned->language) }}</span>
                </div>
                <h2 class="font-serif text-3xl sm:text-4xl font-extrabold leading-tight group-hover:text-base-content/60 transition-colors">{{ $pinned->title }}</h2>
                @if($pinned->summary)
                    <p class="text-base-content/60 mt-3 text-base leading-relaxed line-clamp-2">{{ $pinned->summary }}</p>
                @endif
            </a>
        @endforeach
    </section>
    @endif

    <hr class="border-base-300 mb-10">

    <section>
        <div class="space-y-10">
            @forelse($latestPosts as $post)
                @include('blog::partials.post-card', ['post' => $post])
            @empty
                <p class="text-base-content/40 text-center py-10">{{ __('blog::ui.no_posts_yet') }}</p>
            @endforelse
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('blog.posts.index') }}" class="text-sm font-semibold text-base-content/60 hover:text-primary transition-colors border-b border-base-300 pb-0.5">
                View all articles &rarr;
            </a>
        </div>
    </section>
</div>
@endsection
