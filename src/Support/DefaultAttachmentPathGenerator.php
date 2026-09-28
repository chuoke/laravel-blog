<?php

namespace Chuoke\Blog\Support;

use Chuoke\Blog\Contracts\AttachmentPathGenerator;
use Illuminate\Support\Str;

class DefaultAttachmentPathGenerator implements AttachmentPathGenerator
{
    public function generate(string $fileName, string $extension, ?string $directory = null): string
    {
        $directory = trim($directory ?? config('blog.attachment.directory', 'blog/attachments'), '/');

        return trim($directory.'/'.Str::uuid().'.'.$extension, '/');
    }
}
