@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $pinnedPosts = Blog::pinnedPosts(5);
    $latestPosts = Blog::latestPosts(8);
    $popularPosts = Blog::popularPosts(4);
@endphp

@section('title', config('app.name') . ' — Magazine')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Hero Grid: 1 big + 2 small --}}
    @if($pinnedPosts->isNotEmpty())
    <section class="mb-12">
        <div class="grid md:grid-cols-2 gap-1">
            {{-- Big hero --}}
            @if($hero = $pinnedPosts->first())
            <a href="{{ route('blog.posts.show', $hero->slug) }}" class="group relative block aspect-[4/3] md:aspect-auto overflow-hidden bg-neutral">
                @if($hero->coverImage)
                    <img src="{{ $hero->coverImage->url }}" alt="{{ $hero->title }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8">
                    @if($hero->category)
                        <span class="badge badge-primary badge-sm font-bold uppercase text-[10px] mb-3">{{ $hero->category->name[$locale] ?? array_values($hero->category->name)[0] ?? '' }}</span>
                    @endif
                    <h2 class="font-serif text-2xl md:text-3xl font-bold text-white leading-snug line-clamp-3">{{ $hero->title }}</h2>
                    <p class="text-white/60 text-sm mt-2">{{ $hero->published_at?->format('M d, Y') }}</p>
                </div>
            </a>
            @endif

            {{-- 2 stacked small --}}
            <div class="grid grid-rows-2 gap-1">
                @foreach($pinnedPosts->skip(1)->take(2) as $sub)
                <a href="{{ route('blog.posts.show', $sub->slug) }}" class="group relative block overflow-hidden bg-neutral">
                    @if($sub->coverImage)
                        <img src="{{ $sub->coverImage->url }}" alt="{{ $sub->title }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-5">
                        @if($sub->category)
                            <span class="badge badge-primary badge-xs font-bold uppercase text-[9px] mb-2">{{ $sub->category->name[$locale] ?? array_values($sub->category->name)[0] ?? '' }}</span>
                        @endif
                        <h3 class="font-serif text-lg font-bold text-white leading-snug line-clamp-2">{{ $sub->title }}</h3>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Content + Sidebar --}}
    <div class="flex flex-col lg:flex-row gap-10">
        {{-- Main --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-6 border-b-2 border-primary pb-2">
                <h2 class="text-lg font-extrabold uppercase tracking-wider">Latest</h2>
            </div>

            <div class="grid sm:grid-cols-2 gap-6">
                @forelse($latestPosts as $post)
                    @include('blog::partials.post-card', ['post' => $post])
                @empty
                    <p class="text-base-content/40 col-span-2 text-center py-10">No posts yet.</p>
                @endforelse
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('blog.posts.index') }}" class="btn btn-outline btn-primary btn-sm uppercase tracking-wider">View all articles</a>
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="w-full lg:w-80 flex-shrink-0 space-y-8">
            {{-- Popular --}}
            <div>
                <div class="flex items-center gap-3 mb-4 border-b-2 border-secondary pb-2">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider">Trending</h3>
                </div>
                <ul class="space-y-4">
                    @foreach($popularPosts as $index => $pop)
                        <li>
                            <a href="{{ route('blog.posts.show', $pop->slug) }}" class="group flex gap-3">
                                <span class="text-3xl font-black text-primary/20 leading-none tabular-nums">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <div>
                                    <p class="text-sm font-bold group-hover:text-primary transition-colors line-clamp-2 leading-snug">{{ $pop->title }}</p>
                                    <p class="text-xs text-base-content/40 mt-1">{{ $pop->published_at?->diffForHumans() }}</p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            @include('blog::partials.sidebar')
        </aside>
    </div>
</div>
@endsection
