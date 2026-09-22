<?php

namespace Chuoke\Blog\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uid
 * @property int|null $article_id
 * @property string $author_id
 * @property int|null $category_id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string $content
 * @property string $status
 * @property \Carbon\Carbon|null $published_at
 * @property bool $is_pinned
 * @property int|null $cover_image_id
 * @property string $source_type
 * @property string|null $source_url
 * @property string $language
 * @property int $view_count
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Category|null $category
 * @property-read Collection<int, Tag> $tags
 * @property-read self|null $original
 * @property-read Collection<int, self> $translations
 * @property-read Attachment|null $coverImage
 * @property-read Model $author
 */
class Post extends Model
{
    use SoftDeletes;

    protected $table = 'blog_posts';

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
        'is_pinned' => 'boolean',
    ];

    /**
     * Check if this post is the original (first) post of an article group.
     */
    public function isOriginal(): bool
    {
        return $this->article_id === $this->id;
    }

    /**
     * Check if this post is a translation of another post.
     */
    public function isTranslation(): bool
    {
        return $this->article_id !== null && $this->article_id !== $this->id;
    }

    /**
     * Get the original post of this article group.
     */
    public function original(): BelongsTo
    {
        return $this->belongsTo(self::class, 'article_id');
    }

    /**
     * Get all translations in this article group (excluding self).
     */
    public function translations(): HasMany
    {
        return $this->hasMany(self::class, 'article_id', 'article_id');
    }

    /**
     * Get all sibling posts in this article group (including self).
     */
    public function siblings(): HasMany
    {
        return $this->hasMany(self::class, 'article_id', 'article_id');
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'cover_image_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blog_post_tag', 'article_id', 'tag_id', 'article_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(config('blog.author_model'));
    }
}
