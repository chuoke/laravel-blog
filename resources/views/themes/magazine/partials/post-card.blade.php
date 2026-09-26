@php
    $locale = app()->getLocale();
    $storyNumber = isset($loop) ? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) : '—';
@endphp

<article class="magazine-story group grid grid-cols-[2.75rem_minmax(0,1fr)] gap-3 py-6 sm:grid-cols-[3.5rem_minmax(0,1fr)] sm:gap-5">
    <span class="font-serif text-2xl font-bold leading-none text-base-content/25 tabular-nums sm:text-3xl">{{ $storyNumber }}</span>
    <div class="grid gap-4 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-5">
        <a href="{{ route('blog.posts.show', $post) }}" class="order-2 overflow-hidden bg-base-200 sm:order-1 sm:aspect-[4/3]">
            @if($post->coverImage)
                <img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" loading="lazy" width="320" height="240" class="h-full w-full object-cover transition-transform duration-500 [@media(hover:hover)]:group-hover:scale-105">
            @else
                <span class="flex h-full min-h-24 items-center justify-center font-serif text-4xl font-bold text-base-content/15">{{ mb_substr($post->title, 0, 1) }}</span>
            @endif
        </a>
        <div class="order-1 sm:order-2">
            <div class="mb-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-[10px] font-bold uppercase tracking-[0.14em]">
                @if($post->category)
                    <a href="{{ route('blog.category.show', $post->category->slug) }}" class="text-primary transition-colors hover:text-base-content">{{ $post->category->name[$locale] ?? array_values($post->category->name)[0] ?? '' }}</a>
                    <span class="text-base-content/25">/</span>
                @endif
                <time datetime="{{ $post->published_at?->toDateString() }}" class="text-base-content/45">{{ Blog::formatDate($post->published_at, 'short', $post->language) }}</time>
            </div>
            <a href="{{ route('blog.posts.show', $post) }}" class="block">
                <h2 class="font-serif text-xl font-bold leading-[1.12] tracking-[-0.02em] text-balance transition-colors duration-200 [@media(hover:hover)]:group-hover:text-primary sm:text-2xl">{{ $post->title }}</h2>
            </a>
            @if($post->summary)
                <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-base-content/60 text-pretty">{{ $post->summary }}</p>
            @endif
        </div>
    </div>
</article>
