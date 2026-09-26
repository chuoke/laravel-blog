<?php

namespace Chuoke\Blog\Models;

use Carbon\Carbon;
use Chuoke\Blog\Support\BlogLocale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
 * @property Carbon|null $published_at
 * @property bool $is_pinned
 * @property int|null $cover_image_id
 * @property string $source_type
 * @property string|null $source_url
 * @property string $language
 * @property int $view_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
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

    protected $appends = ['language_label', 'is_translation'];

    public function languageOption(): BlogLocale
    {
        return BlogLocale::from($this->language);
    }

    public function getLanguageLabelAttribute(): string
    {
        return $this->languageOption()->label;
    }

    public function getRouteKey(): string
    {
        return "{$this->uid}-{$this->slug}";
    }

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

    public function getIsTranslationAttribute(): bool
    {
        return $this->isTranslation();
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
