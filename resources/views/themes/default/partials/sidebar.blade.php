@php $locale = app()->getLocale(); @endphp

@php $categories = \Chuoke\Blog\Facades\Blog::categoriesWithCount(); @endphp
@if($categories->isNotEmpty())
<div class="rounded-box bg-base-100 p-6 shadow-[0_1px_2px_rgba(15,15,15,0.04),0_8px_24px_-16px_rgba(15,15,15,0.12)]">
    <h3 class="font-display font-bold text-xs uppercase tracking-[0.14em] text-base-content/40 mb-4">{{ __('blog::ui.categories') }}</h3>
    <ul class="space-y-1">
        @foreach($categories as $cat)
            <li>
                <a href="{{ route('blog.category.show', $cat->slug) }}"
                   class="flex items-center justify-between py-2 px-2.5 -mx-2.5 rounded-field hover:bg-base-200 transition-colors duration-150 group">
                    <span class="text-sm text-base-content/75 group-hover:text-primary font-medium transition-colors duration-150">{{ $cat->name[$locale] ?? array_values($cat->name)[0] ?? '' }}</span>
                    <span class="text-[11px] bg-base-200 text-base-content/50 px-2 py-0.5 rounded-full font-bold tabular-nums">{{ $cat->posts_count }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
@endif

@php $tags = \Chuoke\Blog\Facades\Blog::tagsWithCount(); @endphp
@if($tags->isNotEmpty())
<div class="rounded-box bg-base-100 p-6 shadow-[0_1px_2px_rgba(15,15,15,0.04),0_8px_24px_-16px_rgba(15,15,15,0.12)]">
    <h3 class="font-display font-bold text-xs uppercase tracking-[0.14em] text-base-content/40 mb-4">{{ __('blog::ui.tags') }}</h3>
    <div class="flex flex-wrap gap-2">
        @foreach($tags as $t)
            <a href="{{ route('blog.tag.show', $t->slug) }}"
               class="text-xs font-semibold text-base-content/65 bg-base-200 hover:bg-primary/10 hover:text-primary px-3 py-1.5 rounded-selector transition-colors duration-150">
                {{ $t->name[$locale] ?? array_values($t->name)[0] ?? '' }}
                <span class="text-base-content/35 ml-0.5">{{ $t->posts_count }}</span>
            </a>
        @endforeach
    </div>
</div>
@endif

@php $archives = \Chuoke\Blog\Facades\Blog::archives(); @endphp
@if($archives->isNotEmpty())
<div class="rounded-box bg-base-100 p-6 shadow-[0_1px_2px_rgba(15,15,15,0.04),0_8px_24px_-16px_rgba(15,15,15,0.12)]">
    <h3 class="font-display font-bold text-xs uppercase tracking-[0.14em] text-base-content/40 mb-4">{{ __('blog::ui.archive') }}</h3>
    <ul class="space-y-1">
        @foreach($archives as $archive)
            <li class="flex items-center justify-between py-2 px-2.5 -mx-2.5 rounded-field hover:bg-base-200 transition-colors duration-150">
                <span class="text-sm text-base-content/75 font-medium">{{ Blog::formatDate(\Carbon\Carbon::create($archive->year, $archive->month), 'month') }}</span>
                <span class="text-[11px] bg-base-200 text-base-content/50 px-2 py-0.5 rounded-full font-bold tabular-nums">{{ $archive->count }}</span>
            </li>
        @endforeach
    </ul>
</div>
@endif
