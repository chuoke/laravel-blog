@php $locale = app()->getLocale(); @endphp

{{-- Categories --}}
<div>
    <div class="flex items-center gap-3 mb-4 border-b-2 border-accent pb-2">
        <h3 class="text-sm font-extrabold uppercase tracking-wider">Categories</h3>
    </div>
    <ul class="space-y-2">
        @foreach(\Chuoke\Blog\Facades\Blog::categoriesWithCount() as $cat)
            <li>
                <a href="{{ route('blog.category.show', $cat->slug) }}" class="flex items-center justify-between text-sm text-base-content/70 hover:text-primary transition-colors font-medium">
                    <span>{{ $cat->name[$locale] ?? array_values($cat->name)[0] ?? '' }}</span>
                    <span class="text-xs text-base-content/30 tabular-nums">({{ $cat->posts_count }})</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>

{{-- Tags --}}
<div>
    <div class="flex items-center gap-3 mb-4 border-b-2 border-accent pb-2">
        <h3 class="text-sm font-extrabold uppercase tracking-wider">Tags</h3>
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach(\Chuoke\Blog\Facades\Blog::tagsWithCount() as $t)
            <a href="{{ route('blog.tag.show', $t->slug) }}" class="text-xs font-semibold text-base-content/60 border border-base-300 hover:border-primary hover:text-primary px-3 py-1 transition-colors">
                {{ $t->name[$locale] ?? array_values($t->name)[0] ?? '' }}
            </a>
        @endforeach
    </div>
</div>
