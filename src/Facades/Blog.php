<?php

namespace Chuoke\Blog\Facades;

use Illuminate\Support\Facades\Facade;
use Chuoke\Blog\BlogManager;

/**
 * @method static \Illuminate\Database\Eloquent\Collection latestPosts(int $limit = 10, ?string $language = null)
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator paginatedPosts(array $filters = [], int $perPage = 15, ?string $language = null)
 * @method static \Illuminate\Database\Eloquent\Collection pinnedPosts(int $limit = 5, ?string $language = null)
 * @method static \Illuminate\Database\Eloquent\Collection popularPosts(int $limit = 5, ?string $language = null)
 * @method static \Chuoke\Blog\Models\Post|null post(string $slug, ?string $language = null)
 * @method static \Illuminate\Database\Eloquent\Collection relatedPosts(\Chuoke\Blog\Models\Post $post, int $limit = 5)
 * @method static \Chuoke\Blog\Models\Post|null previousPost(\Chuoke\Blog\Models\Post $post)
 * @method static \Chuoke\Blog\Models\Post|null nextPost(\Chuoke\Blog\Models\Post $post)
 * @method static \Chuoke\Blog\Models\Category|null category(string $slug)
 * @method static \Illuminate\Database\Eloquent\Collection categoriesWithCount(?string $language = null)
 * @method static \Illuminate\Database\Eloquent\Collection categories()
 * @method static \Chuoke\Blog\Models\Tag|null tag(string $slug)
 * @method static \Illuminate\Database\Eloquent\Collection tagsWithCount(?string $language = null)
 * @method static \Illuminate\Database\Eloquent\Collection tags()
 * @method static \Illuminate\Support\Collection archives(?string $language = null)
 *
 * @see \Chuoke\Blog\BlogManager
 */
class Blog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BlogManager::class;
    }
}
