<?php

namespace Chuoke\Blog\Dtos;

readonly class AttachmentCreateData
{
    public function __construct(
        public string $path,
        public string $fileName,
        public ?string $extension = null,
        public string $type = 'image',
        public ?string $mimeType = null,
        public int $size = 0,
        public ?int $width = null,
        public ?int $height = null,
        public ?string $alt = null,
        public ?string $hash = null,
        public string $disk = 'public',
    ) {
    }
}
