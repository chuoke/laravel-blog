<?php

namespace Chuoke\Blog\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property array<string, string> $name Keyed by locale, e.g. ['en' => 'Tech', 'zh' => '技术']
 * @property string $slug
 * @property array<string, string>|null $description Keyed by locale
 * @property int|null $parent_id
 * @property int $sort_order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read Category|null $parent
 * @property-read Collection<int, Category> $children
 * @property-read Collection<int, Post> $posts
 */
class Category extends Model
{
    protected $table = 'blog_categories';

    protected $guarded = ['id'];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
