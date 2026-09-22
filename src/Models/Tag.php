<?php

namespace Chuoke\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property array<string, string> $name Keyed by locale, e.g. ['en' => 'Laravel', 'zh' => 'Laravel']
 * @property string $slug
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Post> $posts
 */
class Tag extends Model
{
    protected $table = 'blog_tags';

    protected $guarded = ['id'];

    protected $casts = [
        'name' => 'array',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'blog_post_tag', 'tag_id', 'article_id', 'id', 'article_id');
    }
}
