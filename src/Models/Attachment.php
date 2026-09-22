<?php

namespace Chuoke\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $disk
 * @property string $path
 * @property string $file_name
 * @property string|null $extension
 * @property string $type
 * @property string|null $mime_type
 * @property int $size
 * @property int|null $width
 * @property int|null $height
 * @property string|null $alt
 * @property string|null $hash
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read string $url
 * @property-read bool $is_image
 */
class Attachment extends Model
{
    protected $table = 'blog_attachments';

    protected $fillable = [
        'disk',
        'path',
        'file_name',
        'extension',
        'type',
        'mime_type',
        'size',
        'width',
        'height',
        'alt',
        'hash',
    ];

    protected $appends = [
        'url',
    ];

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getIsImageAttribute(): bool
    {
        return $this->type === 'image';
    }
}
