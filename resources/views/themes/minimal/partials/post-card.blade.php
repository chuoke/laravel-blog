@php $locale = app()->getLocale(); @endphp

<article class="group">
    <a href="{{ route('blog.posts.show', $post) }}" class="block">
        <div class="flex items-center gap-2 text-xs text-base-content/40 mb-2 font-medium">
            @if($post->category)
                <span class="text-base-content/70">{{ $post->category->name[$locale] ?? array_values($post->category->name)[0] ?? '' }}</span>
                <span>&mdash;</span>
            @endif
            <span>{{ Blog::formatDate($post->published_at, 'short', $post->language) }}</span>
        </div>
        <h2 class="font-serif text-xl font-bold leading-snug group-hover:text-primary transition-colors">{{ $post->title }}</h2>
        @if($post->summary)
            <p class="text-base-content/60 mt-1.5 text-sm leading-relaxed line-clamp-2">{{ $post->summary }}</p>
        @endif
    </a>
    @if($post->tags->isNotEmpty())
        <div class="flex flex-wrap gap-2 mt-2">
            @foreach($post->tags->take(3) as $t)
                <a href="{{ route('blog.tag.show', $t->slug) }}" class="text-[11px] text-base-content/40 hover:text-primary transition-colors">
                    #{{ $t->name[$locale] ?? array_values($t->name)[0] ?? '' }}
                </a>
            @endforeach
        </div>
    @endif
</article>
