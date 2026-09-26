<?php

namespace Chuoke\Blog;

use Carbon\CarbonInterface;
use Chuoke\Blog\Models\Category;
use Chuoke\Blog\Models\Post;
use Chuoke\Blog\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class BlogManager
{
    /**
     * Request-level cache to avoid duplicate queries within a single request.
     */
    protected array $cache = [];

    public function formatDate(?CarbonInterface $date, string $format = 'short', ?string $locale = null): ?string
    {
        if ($date === null) {
            return null;
        }

        $pattern = match ($format) {
            'long' => 'LL',
            'full' => 'LLLL',
            'month' => 'MMMM YYYY',
            'monthName' => 'MMM',
            'day' => 'D',
            'year' => 'YYYY',
            default => 'll',
        };

        return $date->copy()
            ->locale($locale ?? config('blog.locale', app()->getLocale()))
            ->isoFormat($pattern);
    }

    public function formatRelativeDate(?CarbonInterface $date, ?string $locale = null): ?string
    {
        return $date?->copy()
            ->locale($locale ?? config('blog.locale', app()->getLocale()))
            ->diffForHumans();
    }

    /**
     * Get the latest published posts
     */
    public function latestPosts(int $limit = 10, ?string $language = null): Collection
    {
        $language = $language ?? app()->getLocale();

        return $this->rememberFront("latest:{$language}:{$limit}", fn () => $this->basePostQuery($language)
            ->orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get());
    }

    /**
     * Get published posts with complex filtering & pagination
     * Available filters: search, category_id, tag_id, author_id
     */
    public function paginatedPosts(array $filters = [], int $perPage = 15, ?string $language = null): LengthAwarePaginator
    {
        $query = $this->basePostQuery($language);

        if (! empty($filters['search'])) {
            // Escape LIKE wildcards in user input so a literal '%' or '_' in the
            // search term isn't treated as a wildcard. An explicit ESCAPE clause
            // is required for this to work consistently across drivers (SQLite
            // doesn't apply a default LIKE escape character like MySQL/Postgres do).
            $like = '%'.addcslashes($filters['search'], '\\%_').'%';
            $escapeClause = "ESCAPE '\\'"; // a single backslash as the LIKE escape character

            $query->where(function ($q) use ($like, $escapeClause) {
                $q->whereRaw("title LIKE ? {$escapeClause}", [$like])
                    ->orWhereRaw("summary LIKE ? {$escapeClause}", [$like])
                    ->orWhereRaw("content LIKE ? {$escapeClause}", [$like]);
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['tag_id'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('blog_tags.id', $filters['tag_id']);
            });
        }

        if (! empty($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }

        return $query->orderBy('published_at', 'desc')->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Get pinned/featured posts
     */
    public function pinnedPosts(int $limit = 5, ?string $language = null): Collection
    {
        $language = $language ?? app()->getLocale();

        return $this->rememberFront("pinned:{$language}:{$limit}", fn () => $this->basePostQuery($language)
            ->where('is_pinned', true)
            ->orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get());
    }

    /**
     * Get the most viewed posts
     */
    public function popularPosts(int $limit = 5, ?string $language = null): Collection
    {
        $language = $language ?? app()->getLocale();

        return $this->rememberFront("popular:{$language}:{$limit}", fn () => $this->basePostQuery($language)
            ->orderBy('view_count', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get());
    }

    /**
     * Get a specific post by slug
     */
    public function post(string $uid, ?string $language = null): ?Post
    {
        return $this->basePostQuery($language)
            ->where('uid', $uid)
            ->first();
    }

    /**
     * Get related posts (by sharing category or tags)
     */
    public function relatedPosts(Post $post, int $limit = 5): Collection
    {
        $tagIds = $post->tags->pluck('id');

        return $this->basePostQuery($post->language)
            ->where('id', '!=', $post->id)
            ->where(function ($query) use ($post, $tagIds) {
                if ($post->category_id) {
                    $query->orWhere('category_id', $post->category_id);
                }
                if ($tagIds->isNotEmpty()) {
                    $query->orWhereHas('tags', function ($q) use ($tagIds) {
                        $q->whereIn('blog_tags.id', $tagIds);
                    });
                }
            })
            ->orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the previous post (chronologically)
     */
    public function previousPost(Post $post): ?Post
    {
        return $this->basePostQuery($post->language)
            ->where(function ($query) use ($post) {
                $query->where('published_at', '<', $post->published_at)
                    ->orWhere(function ($q) use ($post) {
                        $q->where('published_at', '=', $post->published_at)
                            ->where('id', '<', $post->id);
                    });
            })
            ->orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Get the next post (chronologically)
     */
    public function nextPost(Post $post): ?Post
    {
        return $this->basePostQuery($post->language)
            ->where(function ($query) use ($post) {
                $query->where('published_at', '>', $post->published_at)
                    ->orWhere(function ($q) use ($post) {
                        $q->where('published_at', '=', $post->published_at)
                            ->where('id', '>', $post->id);
                    });
            })
            ->orderBy('published_at', 'asc')
            ->orderBy('id', 'asc')
            ->first();
    }

    /**
     * Get a specific category by slug
     */
    public function category(string $slug): ?Category
    {
        return Category::where('slug', $slug)->first();
    }

    /**
     * Get categories that have published posts, including the post count.
     * Result is cached for the current request.
     */
    public function categoriesWithCount(?string $language = null): Collection
    {
        $language = $language ?? app()->getLocale();
        $key = "categoriesWithCount:{$language}";

        return $this->remember($key, fn () => $this->rememberFront("categories-with-count:{$language}", function () use ($language) {
            $publishedScope = function ($query) use ($language) {
                $query->where('status', 'published')
                    ->where('language', $language)
                    ->where('published_at', '<=', now());
            };

            // whereHas() + withCount() (rather than a HAVING clause on the
            // withCount subquery alias) is used because SQLite - unlike
            // MySQL - rejects a HAVING clause that isn't over an
            // aggregate/GROUP BY expression.
            return Category::withCount(['posts' => $publishedScope])
                ->whereHas('posts', $publishedScope)
                ->orderBy('sort_order')
                ->get();
        }));
    }

    /**
     * Get all categories
     */
    public function categories(): Collection
    {
        return $this->remember('categories', fn () => $this->rememberFront('categories', fn () => Category::orderBy('sort_order')->get()));
    }

    /**
     * Get a specific tag by slug
     */
    public function tag(string $slug): ?Tag
    {
        return Tag::where('slug', $slug)->first();
    }

    /**
     * Get tags that have published posts, including the post count (for Tag Cloud).
     * Result is cached for the current request.
     */
    public function tagsWithCount(?string $language = null): Collection
    {
        $language = $language ?? app()->getLocale();
        $key = "tagsWithCount:{$language}";

        return $this->remember($key, fn () => $this->rememberFront("tags-with-count:{$language}", function () use ($language) {
            $publishedScope = function ($query) use ($language) {
                $query->where('status', 'published')
                    ->where('language', $language)
                    ->where('published_at', '<=', now());
            };

            return Tag::withCount(['posts' => $publishedScope])
                ->whereHas('posts', $publishedScope)
                ->get();
        }));
    }

    /**
     * Get all tags
     */
    public function tags(): Collection
    {
        return $this->remember('tags', fn () => $this->rememberFront('tags', fn () => Tag::all()));
    }

    /**
     * Get monthly archives with post counts (e.g. "January 2024 (5)").
     * Result is cached for the current request.
     */
    public function archives(?string $language = null): \Illuminate\Support\Collection
    {
        $language = $language ?? app()->getLocale();
        $key = "archives:{$language}";

        return $this->remember($key, fn () => $this->rememberFront("archives:{$language}", function () use ($language) {
            // Grouped in PHP rather than via SQL YEAR()/MONTH() (MySQL-only
            // functions not supported by SQLite/Postgres) to keep this
            // portable across database drivers.
            return Post::where('status', 'published')
                ->where('language', $language)
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->pluck('published_at')
                ->groupBy(fn ($date) => $date->format('Y-m'))
                // Cast to an object (rather than a plain array) so entries keep
                // the same `$archive->year` / `->month` / `->count` access the
                // previous stdClass-like query results and the theme views used.
                ->map(fn ($group, $yearMonth) => (object) [
                    'year' => (int) substr($yearMonth, 0, 4),
                    'month' => (int) substr($yearMonth, 5, 2),
                    'count' => $group->count(),
                ])
                ->values();
        }));
    }

    /**
     * Increment view count for a post (fire-and-forget).
     */
    public function recordView(Post $post): void
    {
        $post->increment('view_count');
    }

    public function forgetFrontCache(): void
    {
        $this->cache = [];

        Cache::add('blog:front:version', 1);
        Cache::increment('blog:front:version');
    }

    /**
     * Build the base query for published posts
     */
    protected function basePostQuery(?string $language = null)
    {
        $query = Post::with(['author', 'category', 'tags', 'coverImage'])
            ->where('status', 'published')
            ->where('published_at', '<=', now());

        $language = $language ?? app()->getLocale();
        $query->where('language', $language);

        return $query;
    }

    /**
     * Request-level memoization. Prevents duplicate DB queries
     * when the same data is used in multiple partials.
     */
    protected function remember(string $key, callable $callback): mixed
    {
        if (! array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $callback();
        }

        return $this->cache[$key];
    }

    protected function rememberFront(string $key, callable $callback): mixed
    {
        $version = Cache::rememberForever('blog:front:version', fn () => 1);
        $ttl = (int) config('blog.cache.front_ttl', 300);
        $jitter = $ttl > 0 ? random_int(1, max(1, intdiv($ttl, 10))) : 0;

        return Cache::remember(
            "blog:front:v{$version}:{$key}",
            now()->addSeconds($ttl + $jitter),
            $callback,
        );
    }
}
