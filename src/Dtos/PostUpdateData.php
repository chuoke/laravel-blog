<?php

namespace Chuoke\Blog\Dtos;

readonly class PostUpdateData
{
    public function __construct(
        public ?string $title = null,
        public ?string $content = null,
        public ?string $summary = null,
        public ?int $categoryId = null,
        public ?array $tagIds = null,
        public ?int $coverImageId = null,
        public ?string $sourceType = null,
        public ?string $sourceUrl = null,
    ) {
    }
}
