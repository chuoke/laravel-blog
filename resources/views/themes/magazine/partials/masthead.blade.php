@php
    $issueDate = now();
@endphp

<section class="magazine-masthead border-y border-base-content/70 py-3 text-base-content">
    <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
        <time datetime="{{ $issueDate->toDateString() }}" class="text-[10px] font-bold uppercase tracking-[0.16em] text-base-content/55">
            {{ Blog::formatDate($issueDate, 'full') }}
        </time>
        <a href="{{ route('blog.home') }}" class="font-serif text-2xl font-black tracking-[-0.04em] sm:text-3xl">
            {{ config('app.name') }} <span class="font-sans text-[0.48em] font-bold uppercase tracking-[0.18em]">{{ __('blog::ui.brand_suffix') }}</span>
        </a>
        <span class="justify-self-end text-[10px] font-bold uppercase tracking-[0.16em] text-base-content/55">{{ __('blog::ui.issue_no', ['year' => $issueDate->format('Y')]) }}</span>
    </div>

    <nav aria-label="Magazine sections" class="mt-3 flex items-center justify-center gap-x-5 border-t border-base-content/15 pt-3 text-[10px] font-bold uppercase tracking-[0.14em] text-base-content/60 sm:gap-x-8">
        <a href="{{ route('blog.home') }}" class="transition-colors hover:text-primary">{{ __('blog::ui.front_page') }}</a>
        <a href="{{ route('blog.posts.index') }}" class="transition-colors hover:text-primary">{{ __('blog::ui.latest') }}</a>
        @foreach(Blog::categories()->take(3) as $navCategory)
            <a href="{{ route('blog.category.show', $navCategory->slug) }}" class="hidden transition-colors hover:text-primary sm:block">
                {{ $navCategory->name[app()->getLocale()] ?? array_values($navCategory->name)[0] ?? '' }}
            </a>
        @endforeach
    </nav>
</section>
