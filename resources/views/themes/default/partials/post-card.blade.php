@php $locale = app()->getLocale(); @endphp

<article class="group h-full flex flex-col">
    <a href="{{ route('blog.posts.show', $post->slug) }}" class="block aspect-[16/10] rounded-box overflow-hidden mb-4 shadow-[0_1px_2px_rgba(15,15,15,0.04),0_8px_20px_-14px_rgba(15,15,15,0.18)] transition-shadow duration-300 group-hover:shadow-[0_1px_2px_rgba(15,15,15,0.05),0_20px_36px_-16px_rgba(15,15,15,0.28)]">
        @if($post->coverImage)
            <img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" loading="lazy" width="480" height="300" class="w-full h-full object-cover outline outline-1 outline-black/10 -outline-offset-1 transition-transform duration-500 group-hover:scale-[1.04]">
        @else
            <div class="w-full h-full bg-base-300 flex items-center justify-center">
                <span class="font-display font-extrabold text-4xl text-base-content/15">{{ mb_substr($post->title, 0, 1) }}</span>
            </div>
        @endif
    </a>

    <div class="flex items-center gap-2 mb-2 text-xs">
        @if($post->category)
            <a href="{{ route('blog.category.show', $post->category->slug) }}" class="font-semibold text-primary hover:underline">{{ $post->category->name[$locale] ?? array_values($post->category->name)[0] ?? '' }}</a>
            <span class="text-base-content/25">&bull;</span>
        @endif
        <span class="text-base-content/45 font-medium">{{ $post->published_at?->format('M d, Y') }}</span>
    </div>

    <a href="{{ route('blog.posts.show', $post->slug) }}">
        <h3 class="font-display font-bold text-[1.05rem] leading-snug tracking-[-0.005em] group-hover:text-primary transition-colors duration-150 line-clamp-2">{{ $post->title }}</h3>
    </a>

    @if($post->summary)
        <p class="text-sm text-base-content/55 mt-2 line-clamp-2 leading-relaxed text-pretty">{{ $post->summary }}</p>
    @endif

    @if($post->tags->isNotEmpty())
        <div class="flex flex-wrap gap-1.5 mt-auto pt-3">
            @foreach($post->tags->take(3) as $t)
                <a href="{{ route('blog.tag.show', $t->slug) }}" class="text-[11px] font-semibold text-base-content/55 bg-base-200 hover:bg-primary/10 hover:text-primary px-2.5 py-1 rounded-selector transition-colors duration-150">
                    #{{ $t->name[$locale] ?? array_values($t->name)[0] ?? '' }}
                </a>
            @endforeach
        </div>
    @endif
</article>
