<?php

namespace Chuoke\Blog\Support;

use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\Category;
use Chuoke\Blog\Models\Tag;
use Illuminate\Support\Str;

class SeoMeta
{
    /**
     * @return array{title:string, description:string, canonical:string, robots:?string}
     */
    public function metadata(string $sectionTitle, string $sectionDescription, bool $useDefaultTitle = false): array
    {
        $routeName = request()->route()?->getName();
        $resource = $this->resource($routeName);

        return [
            'title' => $this->title($routeName, $sectionTitle, $resource, $useDefaultTitle),
            'description' => $this->description($routeName, $sectionDescription, $resource),
            'canonical' => $this->canonical($routeName),
            'robots' => $routeName === 'blog.posts.index' && request()->filled('search') ? 'noindex,follow' : null,
        ];
    }

    private function title(?string $routeName, string $sectionTitle, Category|Tag|null $resource, bool $useDefaultTitle): string
    {
        $title = ! $useDefaultTitle && $sectionTitle !== '' ? $sectionTitle : match ($routeName) {
            'blog.home' => __('blog::ui.seo_home_title'),
            'blog.posts.index' => request()->filled('search')
                ? __('blog::ui.seo_search_title', ['search' => request()->string('search')->trim()->value()])
                : __('blog::ui.seo_archive_title'),
            'blog.category.show' => $resource instanceof Category
                ? __('blog::ui.seo_category_title', ['category' => $this->name($resource)])
                : $sectionTitle,
            'blog.tag.show' => $resource instanceof Tag
                ? __('blog::ui.seo_tag_title', ['tag' => $this->name($resource)])
                : $sectionTitle,
            default => $sectionTitle,
        };

        if ($this->page() > 1) {
            $title = __('blog::ui.seo_page_title', ['title' => $title, 'page' => $this->page()]);
        }

        $site = config('app.name', 'Blog');

        return $title !== '' ? $title.config('blog.seo.title_separator', ' — ').$site : $site;
    }

    private function description(?string $routeName, string $sectionDescription, Category|Tag|null $resource): string
    {
        if ($sectionDescription !== '') {
            return Str::squish($sectionDescription);
        }

        if ($routeName === 'blog.category.show' && $resource instanceof Category) {
            $description = $this->localized($resource->description);

            if ($description !== null) {
                return $description;
            }
        }

        $configuredDescription = $this->configuredDescription();

        if ($configuredDescription !== null) {
            return $configuredDescription;
        }

        $site = config('app.name', 'Blog');

        return match ($routeName) {
            'blog.home' => __('blog::ui.seo_home_description', ['site' => $site]),
            'blog.posts.index' => request()->filled('search')
                ? __('blog::ui.seo_search_description', ['search' => request()->string('search')->trim()->value(), 'site' => $site])
                : __('blog::ui.seo_archive_description', ['site' => $site]),
            'blog.category.show' => $resource instanceof Category
                ? __('blog::ui.seo_category_description', ['category' => $this->name($resource), 'site' => $site])
                : '',
            'blog.tag.show' => $resource instanceof Tag
                ? __('blog::ui.seo_tag_description', ['tag' => $this->name($resource), 'site' => $site])
                : '',
            default => '',
        };
    }

    private function canonical(?string $routeName): string
    {
        if ($routeName !== 'blog.posts.index' || ! request()->filled('search')) {
            return $this->page() > 1 ? request()->url().'?page='.$this->page() : request()->url();
        }

        return request()->url();
    }

    private function page(): int
    {
        return max(1, request()->integer('page', 1));
    }

    private function resource(?string $routeName): Category|Tag|null
    {
        $slug = request()->route()?->parameter('slug');

        return match ($routeName) {
            'blog.category.show' => Blog::category((string) $slug),
            'blog.tag.show' => Blog::tag((string) $slug),
            default => null,
        };
    }

    private function name(Category|Tag $resource): string
    {
        return $this->localized($resource->name) ?? $resource->slug;
    }

    /**
     * @param  array<string, string>|null  $values
     */
    private function localized(?array $values): ?string
    {
        if (empty($values)) {
            return null;
        }

        $locale = app()->getLocale();
        $value = $values[$locale] ?? $values[Str::before($locale, '_')] ?? $values[Str::before($locale, '-')] ?? reset($values);

        return filled($value) ? Str::squish((string) $value) : null;
    }

    private function configuredDescription(): ?string
    {
        $description = config('blog.seo.description', []);

        if (is_string($description)) {
            return filled($description) ? Str::squish($description) : null;
        }

        return is_array($description) ? $this->localized($description) : null;
    }
}
