@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $pinnedPosts = Blog::pinnedPosts(5);
    $latestPosts = Blog::latestPosts(8);
    $popularPosts = Blog::popularPosts(5);

    $lead = $pinnedPosts->first() ?? $latestPosts->first();
    $stories = $latestPosts->reject(fn ($post) => $lead && $post->is($lead));
    $secondaryPosts = $pinnedPosts
        ->reject(fn ($post) => $lead && $post->is($lead))
        ->merge($stories)
        ->unique('id')
        ->take(4);
@endphp

@section('title', config('app.name'))

@section('seo_default', '1')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10">
    @include('blog::partials.masthead')

    @if($lead)
        <section class="grid border-b border-base-content/70 py-8 lg:grid-cols-12 lg:py-10">
            <a href="{{ route('blog.posts.show', $lead) }}" class="group lg:col-span-7 lg:pr-8">
                <div class="aspect-[16/10] overflow-hidden bg-base-200">
                    @if($lead->coverImage)
                        <img src="{{ $lead->coverImage->url }}" alt="{{ $lead->title }}" width="960" height="600" class="h-full w-full object-cover transition-transform duration-700 [@media(hover:hover)]:group-hover:scale-[1.03]">
                    @else
                        <span class="flex h-full items-center justify-center font-serif text-8xl font-bold text-base-content/15">{{ mb_substr($lead->title, 0, 1) }}</span>
                    @endif
                </div>
                <div class="pt-5 sm:pt-6">
                    <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.16em] text-primary">
                        {{ $lead->category?->name[$locale] ?? 'Cover Story' }} · {{ Blog::formatDate($lead->published_at, 'short', $lead->language) }}
                    </p>
                    <h1 class="font-serif text-3xl font-black leading-[1.08] tracking-[-0.04em] text-balance transition-colors duration-200 [@media(hover:hover)]:group-hover:text-primary sm:text-5xl">{{ $lead->title }}</h1>
                    @if($lead->summary)
                        <p class="mt-4 max-w-2xl text-base leading-relaxed text-base-content/65 text-pretty">{{ $lead->summary }}</p>
                    @endif
                </div>
            </a>

            <div class="mt-8 border-t border-base-content/20 pt-2 lg:col-span-5 lg:mt-0 lg:flex lg:h-full lg:flex-col lg:border-t-0 lg:border-l lg:pl-8 lg:pt-0">
                <p class="py-3 text-[10px] font-bold uppercase tracking-[0.16em] text-base-content/45 lg:shrink-0">{{ __('blog::ui.also_in_issue') }}</p>
                <div class="divide-y divide-base-content/20 lg:flex lg:flex-1 lg:flex-col">
                    @foreach($secondaryPosts as $sub)
                        <a href="{{ route('blog.posts.show', $sub) }}" class="group block py-5 first:pt-2 lg:flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-primary">{{ $sub->category?->name[$locale] ?? __('blog::ui.feature') }}</p>
                            <h2 class="mt-2 font-serif text-2xl font-bold leading-[1.08] tracking-[-0.02em] transition-colors duration-200 [@media(hover:hover)]:group-hover:text-primary">{{ $sub->title }}</h2>
                            <p class="mt-3 text-xs font-medium uppercase tracking-[0.12em] text-base-content/45">{{ Blog::formatDate($sub->published_at, 'short', $sub->language) }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="grid gap-10 py-10 lg:grid-cols-12 lg:gap-12">
        <div class="lg:col-span-8">
            <div class="flex items-end justify-between border-b-2 border-base-content pb-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-primary">{{ __('blog::ui.reading_room') }}</p>
                    <h2 class="mt-1 font-serif text-3xl font-black tracking-[-0.03em]">{{ __('blog::ui.latest_posts') }}</h2>
                </div>
                <a href="{{ route('blog.posts.index') }}" class="text-[10px] font-bold uppercase tracking-[0.14em] text-base-content/55 transition-colors hover:text-primary">{{ __('blog::ui.view_archive') }}</a>
            </div>
            <div class="divide-y divide-base-content/20">
                @forelse($stories as $post)
                    @include('blog::partials.post-card', ['post' => $post])
                @empty
                    <p class="py-12 text-center text-base-content/45">{{ __('blog::ui.no_stories_issue') }}</p>
                @endforelse
            </div>
        </div>

        <aside class="border-t border-base-content/70 pt-5 lg:col-span-4 lg:border-t-0 lg:border-l lg:pl-8 lg:pt-0">
            <p class="border-b-2 border-base-content pb-3 text-[10px] font-bold uppercase tracking-[0.16em]">{{ __('blog::ui.most_read') }}</p>
            <ol class="divide-y divide-base-content/15">
                @foreach($popularPosts as $pop)
                    <li class="grid grid-cols-[2rem_minmax(0,1fr)] gap-3 py-4">
                        <span class="font-serif text-2xl font-bold leading-none text-base-content/30 tabular-nums">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <a href="{{ route('blog.posts.show', $pop) }}" class="group">
                            <p class="font-serif text-lg font-bold leading-tight transition-colors duration-200 [@media(hover:hover)]:group-hover:text-primary">{{ $pop->title }}</p>
                            <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.12em] text-base-content/45">{{ __('blog::ui.reads', ['count' => number_format($pop->view_count)]) }}</p>
                        </a>
                    </li>
                @endforeach
            </ol>

            <div class="mt-8 border-t border-base-content/20 pt-6">
                @include('blog::partials.sidebar')
            </div>
        </aside>
    </section>
</div>
@endsection
