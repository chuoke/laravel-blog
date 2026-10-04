<?php

namespace Chuoke\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoverGeneration extends Model
{
    protected $table = 'blog_cover_generations';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class);
    }
}
