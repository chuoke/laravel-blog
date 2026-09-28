<?php

namespace Chuoke\Blog\Support;

use InvalidArgumentException;

class AttachmentPath
{
    public static function normalize(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));

        if ($path === '' || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\//', $path) === 1 || preg_match('#(^|/)\.\.?(/|$)#', $path) === 1) {
            throw new InvalidArgumentException('Attachment paths must be relative to the configured disk.');
        }

        return $path;
    }
}
