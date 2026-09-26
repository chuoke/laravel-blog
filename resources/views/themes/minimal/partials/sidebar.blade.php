@php $locale = app()->getLocale(); @endphp

<div class="py-6 border-t border-base-200">
    <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-base-content/40 mb-3">{{ __('blog::ui.categories') }}</h4>
    <ul class="space-y-1.5">
        @foreach(\Chuoke\Blog\Facades\Blog::categoriesWithCount() as $cat)
            <li>
                <a href="{{ route('blog.category.show', $cat->slug) }}" class="flex items-center justify-between text-sm text-base-content/70 hover:text-primary transition-colors">
                    <span>{{ $cat->name[$locale] ?? array_values($cat->name)[0] ?? '' }}</span>
                    <span class="text-xs text-base-content/40 tabular-nums">{{ $cat->posts_count }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>

<div class="py-6 border-t border-base-200">
    <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-base-content/40 mb-3">{{ __('blog::ui.tags') }}</h4>
    <div class="flex flex-wrap gap-x-3 gap-y-1">
        @foreach(\Chuoke\Blog\Facades\Blog::tagsWithCount() as $t)
            <a href="{{ route('blog.tag.show', $t->slug) }}" class="text-sm text-base-content/60 hover:text-primary transition-colors">
                {{ $t->name[$locale] ?? array_values($t->name)[0] ?? '' }}
            </a>
        @endforeach
    </div>
</div>
