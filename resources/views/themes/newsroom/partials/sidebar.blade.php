@php $locale = app()->getLocale(); @endphp
<div class="border-t-2 border-base-content pt-4">
    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-base-content/60">{{ __('blog::ui.browse_sections') }}</p>
    <ul class="mt-3 divide-y divide-base-content/10">
        @foreach(Blog::categoriesWithCount() as $category)
            <li><a href="{{ route('blog.category.show', $category->slug) }}" class="flex justify-between py-3 text-sm font-semibold transition-colors hover:text-primary"><span>{{ $category->name[$locale] ?? array_values($category->name)[0] ?? '' }}</span><span class="tabular-nums text-base-content/40">{{ $category->posts_count }}</span></a></li>
        @endforeach
    </ul>
</div>
