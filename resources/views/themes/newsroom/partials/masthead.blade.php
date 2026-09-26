@php $editionDate = now(); @endphp

<section class="newsroom-masthead border-b-2 border-base-content pb-4">
    <div class="flex items-center justify-between border-b border-base-content/15 pb-2 text-[10px] font-bold uppercase tracking-[0.14em] text-base-content/50">
        <time datetime="{{ $editionDate->toDateString() }}">{{ Blog::formatDate($editionDate, 'full') }}</time>
        <span>{{ __('blog::ui.daily_edition') }}</span>
    </div>
    <div class="flex flex-col items-center gap-2 py-6 text-center">
        <a href="{{ route('blog.home') }}" class="text-3xl font-black tracking-[-0.05em] text-base-content sm:text-4xl">{{ config('app.name') }} <span class="text-primary">{{ __('blog::ui.brand_suffix') }}</span></a>
        <p class="text-sm font-medium text-base-content/70">{{ __('blog::ui.tagline') }}</p>
    </div>
    <nav aria-label="Newsroom sections" class="flex gap-5 overflow-x-auto border-t border-base-content/15 pt-3 text-xs font-bold text-base-content/75 [scrollbar-width:none] sm:justify-center">
        <a href="{{ route('blog.home') }}" class="shrink-0 transition-colors hover:text-primary">{{ __('blog::ui.top_stories') }}</a>
        <a href="{{ route('blog.posts.index') }}" class="shrink-0 transition-colors hover:text-primary">{{ __('blog::ui.latest') }}</a>
        @foreach(Blog::categories()->take(4) as $category)
            <a href="{{ route('blog.category.show', $category->slug) }}" class="shrink-0 transition-colors hover:text-primary">{{ $category->name[app()->getLocale()] ?? array_values($category->name)[0] ?? '' }}</a>
        @endforeach
    </nav>
</section>
