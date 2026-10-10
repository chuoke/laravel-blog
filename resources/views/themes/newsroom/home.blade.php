@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $latest = Blog::latestPosts(12);
    $popular = Blog::popularPosts(3);
    $lead = Blog::pinnedPosts(1)->first() ?? $latest->first();
    $other = $latest->reject(fn ($post) => $lead && $post->is($lead));
@endphp
@section('title', 'News')
@section('seo_default', '1')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    @include('blog::partials.masthead')
    <section class="newsroom-briefing grid border-b border-base-content/20 py-7 lg:grid-cols-[1fr_1.7fr_1fr]">
        <div class="border-b border-base-content/15 pb-5 lg:border-r lg:border-b-0 lg:pr-6">
            <p class="text-[10px] font-black uppercase tracking-[.14em] text-primary">{{ __('blog::ui.also_in_news') }}</p>
            <div class="mt-3 divide-y divide-base-content/15">@foreach($other->take(4) as $post)<a href="{{ route('blog.posts.show', $post) }}" class="group block py-4"><p class="text-[9px] font-bold uppercase tracking-[.12em] text-primary">{{ $post->category?->name[$locale] ?? __('blog::ui.fallback_news') }}</p><h2 class="mt-1 text-lg font-black leading-snug group-hover:text-primary">{{ $post->title }}</h2>@if($loop->first && $post->summary)<p class="mt-2 line-clamp-3 text-sm leading-relaxed text-base-content/70">{{ $post->summary }}</p>@endif</a>@endforeach</div>
        </div>
        @if($lead)<a href="{{ route('blog.posts.show', $lead) }}" class="group border-b border-base-content/15 py-6 lg:border-r lg:border-b-0 lg:px-7 lg:py-0"><p class="text-[10px] font-black uppercase tracking-[.14em] text-primary">{{ __('blog::ui.lead_story') }}</p><h1 class="mt-3 text-3xl font-black leading-[1.08] tracking-[-.045em] group-hover:text-primary sm:text-4xl">{{ $lead->title }}</h1>@if($lead->summary)<p class="mt-4 text-sm leading-relaxed text-base-content/70">{{ $lead->summary }}</p>@endif<div class="mt-6 aspect-[16/9] bg-base-200">@if($lead->coverImage)<img src="{{ $lead->coverImage->url }}" alt="{{ $lead->title }}" class="h-full w-full object-cover">@endif</div></a>@endif
        <div class="pt-6 lg:pl-6 lg:pt-0"><p class="text-[10px] font-black uppercase tracking-[.14em] text-primary">{{ __('blog::ui.trending') }}</p><div class="mt-3 divide-y divide-base-content/15">@foreach($popular as $post)<a href="{{ route('blog.posts.show', $post) }}" class="group block py-4">@if($loop->first && $post->coverImage)<div class="mb-3 aspect-[16/9] overflow-hidden bg-base-200"><img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition-transform duration-500 [@media(hover:hover)]:group-hover:scale-105"></div>@endif<h2 class="text-lg font-black leading-snug group-hover:text-primary">{{ $post->title }}</h2><p class="mt-1 text-[10px] text-base-content/60">{{ __('blog::ui.reads', ['count' => number_format($post->view_count)]) }}</p></a>@endforeach</div></div>
    </section>
    <section class="py-8"><div class="flex items-center justify-between border-b-2 border-base-content pb-3"><h2 class="text-xl font-black">{{ __('blog::ui.across_ecosystem') }}</h2><a href="{{ route('blog.posts.index') }}" class="text-xs font-bold text-primary">{{ __('blog::ui.view_all') }}</a></div><div class="grid border-b border-base-content/15 max-md:divide-y max-md:divide-base-content/15 md:grid-cols-3 md:divide-x md:divide-base-content/15">@foreach($other->skip(4)->take(3) as $post)<a href="{{ route('blog.posts.show', $post) }}" class="group py-5 md:px-5 first:pl-0"><p class="text-[9px] font-bold uppercase tracking-[.12em] text-primary">{{ $post->category?->name[$locale] ?? __('blog::ui.fallback_news') }}</p><h3 class="mt-2 text-xl font-black leading-snug group-hover:text-primary">{{ $post->title }}</h3>@if($post->summary)<p class="mt-2 text-xs leading-relaxed text-base-content/70">{{ Str::limit($post->summary, 120) }}</p>@endif</a>@endforeach</div></section>
    <section class="border-t border-base-content/20 py-8"><div class="flex justify-between border-b-2 border-base-content pb-3"><h2 class="text-2xl font-black">{{ __('blog::ui.the_latest') }}</h2><a href="{{ route('blog.posts.index') }}" class="text-xs font-bold text-primary">{{ __('blog::ui.view_all') }}</a></div><div class="grid gap-5 py-5 sm:grid-cols-2 lg:grid-cols-3">@foreach($other->skip(7) as $post)<a href="{{ route('blog.posts.show', $post) }}" class="group"><div class="aspect-[16/10] bg-base-200">@if($post->coverImage)<img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" class="h-full w-full object-cover">@endif</div><p class="mt-3 text-[9px] font-bold uppercase tracking-[.12em] text-primary">{{ $post->category?->name[$locale] ?? __('blog::ui.fallback_news') }}</p><h3 class="mt-1 text-base font-black leading-snug group-hover:text-primary">{{ $post->title }}</h3></a>@endforeach</div></section>
</div>
@endsection
