@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $pinnedPosts = Blog::pinnedPosts(3);
    $latestPosts = Blog::latestPosts(7);
    $popularPosts = Blog::popularPosts(5);

    $lead = $pinnedPosts->first() ?? $latestPosts->first();
    $gridPosts = $latestPosts->reject(fn ($p) => $lead && $p->is($lead))->take(6);
@endphp

@section('title', config('app.name') . ' — Blog')

@section('content')
<div class="max-w-6xl mx-auto px-5 sm:px-8 py-12 sm:py-16">

    @if($lead)
        <section class="mb-16 animate-fade-up">
            <a href="{{ route('blog.posts.show', $lead->slug) }}" class="group grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                <div class="order-2 lg:order-1">
                    <div class="flex items-center gap-3 mb-4 text-xs font-semibold uppercase tracking-[0.14em]">
                        <span class="text-secondary">Featured</span>
                        @if($lead->category)
                            <span class="text-base-content/25">/</span>
                            <span class="text-base-content/45">{{ $lead->category->name[$locale] ?? array_values($lead->category->name)[0] ?? '' }}</span>
                        @endif
                    </div>
                    <h1 class="font-display font-extrabold text-[2rem] sm:text-[2.75rem] leading-[1.05] tracking-[-0.02em] text-balance group-hover:text-primary transition-colors duration-200">
                        {{ $lead->title }}
                    </h1>
                    @if($lead->summary)
                        <p class="mt-5 text-base sm:text-lg text-base-content/60 leading-relaxed max-w-lg text-pretty">{{ $lead->summary }}</p>
                    @endif
                    <div class="mt-6 flex items-center gap-3 text-sm text-base-content/45 font-medium">
                        <span>{{ $lead->published_at?->format('M d, Y') }}</span>
                        <span class="w-1 h-1 rounded-full bg-base-content/25"></span>
                        <span>{{ number_format($lead->view_count) }} views</span>
                    </div>
                    <span class="mt-7 inline-flex items-center gap-2 font-display font-semibold text-sm text-primary">
                        Read the story
                        <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </span>
                </div>
                <div class="order-1 lg:order-2 aspect-[4/3] rounded-box overflow-hidden shadow-[0_1px_2px_rgba(15,15,15,0.05),0_24px_48px_-16px_rgba(15,15,15,0.28)]">
                    @if($lead->coverImage)
                        <img src="{{ $lead->coverImage->url }}" alt="{{ $lead->title }}" width="800" height="600" class="w-full h-full object-cover outline outline-1 outline-black/10 -outline-offset-1 transition-transform duration-500 group-hover:scale-[1.03]">
                    @else
                        <div class="w-full h-full bg-neutral flex items-center justify-center">
                            <span class="font-display font-extrabold text-7xl text-neutral-content/20">{{ mb_substr($lead->title, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
            </a>
        </section>
    @endif

    <div class="flex flex-col lg:flex-row gap-14">
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-7">
                <h2 class="font-display font-bold text-lg tracking-[-0.01em]">Latest</h2>
                <a href="{{ route('blog.posts.index') }}" class="text-sm font-semibold text-base-content/45 hover:text-primary transition-colors duration-150">View all</a>
            </div>

            <div class="grid sm:grid-cols-2 gap-x-6 gap-y-10">
                @forelse($gridPosts as $post)
                    <div class="animate-fade-up" style="animation-delay: {{ $loop->index * 70 }}ms">
                        @include('blog::partials.post-card', ['post' => $post])
                    </div>
                @empty
                    <p class="text-base-content/40 py-10 col-span-2 text-center font-medium">No posts yet.</p>
                @endforelse
            </div>
        </div>

        <aside class="w-full lg:w-72 flex-shrink-0 space-y-8">
            @if($popularPosts->isNotEmpty())
                <div class="rounded-box bg-base-100 p-6 shadow-[0_1px_2px_rgba(15,15,15,0.04),0_8px_24px_-16px_rgba(15,15,15,0.12)]">
                    <h3 class="font-display font-bold text-xs uppercase tracking-[0.14em] text-base-content/40 mb-5">Popular</h3>
                    <ul class="space-y-4">
                        @foreach($popularPosts as $pop)
                            <li class="flex items-start gap-3">
                                <span class="font-display font-extrabold text-2xl leading-none text-base-content/15 mt-0.5 tabular-nums">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <a href="{{ route('blog.posts.show', $pop->slug) }}" class="group block min-w-0">
                                    <p class="text-sm font-semibold group-hover:text-primary transition-colors duration-150 line-clamp-2 leading-snug">{{ $pop->title }}</p>
                                    <p class="text-xs text-base-content/40 mt-1 tabular-nums">{{ number_format($pop->view_count) }} views</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @include('blog::partials.sidebar')
        </aside>
    </div>
</div>
@endsection
