@php $locale = app()->getLocale(); @endphp

<article class="group bg-base-100 border border-base-300 overflow-hidden hover:shadow-lg transition-shadow">
    <a href="{{ route('blog.posts.show', $post->slug) }}" class="block">
        @if($post->coverImage)
            <div class="aspect-video overflow-hidden">
                <img src="{{ $post->coverImage->url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </div>
        @else
            <div class="aspect-video bg-base-200 flex items-center justify-center">
                <span class="text-base-content/10 font-black text-5xl">{{ mb_substr($post->title, 0, 1) }}</span>
            </div>
        @endif
    </a>
    <div class="p-5">
        <div class="flex items-center gap-2 mb-2 text-xs">
            @if($post->category)
                <a href="{{ route('blog.category.show', $post->category->slug) }}" class="font-bold text-primary uppercase tracking-wider text-[10px] hover:underline">{{ $post->category->name[$locale] ?? array_values($post->category->name)[0] ?? '' }}</a>
                <span class="text-base-content/20">|</span>
            @endif
            <span class="text-base-content/40 font-medium">{{ $post->published_at?->format('M d, Y') }}</span>
        </div>
        <a href="{{ route('blog.posts.show', $post->slug) }}">
            <h2 class="font-serif text-lg font-bold leading-snug group-hover:text-primary transition-colors line-clamp-2">{{ $post->title }}</h2>
        </a>
        @if($post->summary)
            <p class="text-sm text-base-content/60 mt-2 line-clamp-2 leading-relaxed">{{ $post->summary }}</p>
        @endif
    </div>
</article>
