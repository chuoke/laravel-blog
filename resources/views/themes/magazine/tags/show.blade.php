@extends(config('blog.layout') ?? 'blog::layout')
@php
    $locale = app()->getLocale();
    $posts = Blog::paginatedPosts(['tag_id' => $tag->id], 12);
@endphp

@section('title', '#' . ($tag->name[$locale] ?? array_values($tag->name)[0] ?? 'Tag') . ' — ' . config('app.name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex flex-col lg:flex-row gap-10">
    <div class="flex-1 min-w-0">
        <div class="mb-6 border-b-2 border-secondary pb-2">
            <p class="text-[10px] font-bold uppercase tracking-widest text-secondary">Tag</p>
            <h1 class="text-2xl font-extrabold uppercase tracking-tight">#{{ $tag->name[$locale] ?? array_values($tag->name)[0] }}</h1>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($posts as $post)
                @include('blog::partials.post-card', ['post' => $post])
            @empty
                <p class="col-span-3 text-base-content/40 text-center py-16">No posts with this tag.</p>
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
@endsection
