@php $locale = app()->getLocale(); @endphp

<article class="newsroom-story group grid gap-4 py-6 sm:grid-cols-[11rem_minmax(0,1fr)]">
    <a href="{{ route('blog.posts.show', $post) }}" class="aspect-[16/10] overflow-hidden bg-base-200">
        @if($post->coverImage)
            <img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" loading="lazy" width="352" height="220" class="h-full w-full object-cover transition-transform duration-500 [@media(hover:hover)]:group-hover:scale-105">
        @endif
    </a>
    <div>
        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-primary">{{ $post->category?->name[$locale] ?? __('blog::ui.fallback_news') }} <span class="mx-1 text-base-content/25">/</span> {{ Blog::formatDate($post->published_at, 'short', $post->language) }}</p>
        <a href="{{ route('blog.posts.show', $post) }}" class="mt-2 block"><h2 class="text-xl font-bold leading-snug tracking-[-0.02em] transition-colors [@media(hover:hover)]:group-hover:text-primary">{{ $post->title }}</h2></a>
        @if($post->summary)<p class="mt-2 line-clamp-2 text-sm leading-relaxed text-base-content/70">{{ $post->summary }}</p>@endif
    </div>
</article>
