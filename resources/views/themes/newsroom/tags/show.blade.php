@extends(config('blog.layout') ?? 'blog::layout')
@php $locale = app()->getLocale(); $posts = Blog::paginatedPosts(['tag_id' => $tag->id], 12); @endphp
@section('title', '#'.($tag->name[$locale] ?? array_values($tag->name)[0] ?? __('blog::ui.fallback_tag')))
@section('seo_default', '1')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">@include('blog::partials.masthead')
    <div class="mt-8 grid gap-10 lg:grid-cols-12"><main class="lg:col-span-8"><div class="border-b-2 border-base-content pb-3"><p class="text-[10px] font-bold uppercase tracking-[0.14em] text-primary">{{ __('blog::ui.topic') }}</p><h1 class="mt-1 text-3xl font-black tracking-[-0.04em]">#{{ $tag->name[$locale] ?? array_values($tag->name)[0] }}</h1></div><div class="divide-y divide-base-content/15">@forelse($posts as $post) @include('blog::partials.post-card', ['post' => $post]) @empty <p class="py-12 text-base-content/60">{{ __('blog::ui.no_stories_topic') }}</p> @endforelse</div>@if($posts->hasPages())<div class="mt-8">{{ $posts->withQueryString()->links() }}</div>@endif</main><aside class="lg:col-span-4">@include('blog::partials.sidebar')</aside></div>
</div>
@endsection
