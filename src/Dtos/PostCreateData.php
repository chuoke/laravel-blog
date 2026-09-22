<?php

namespace Chuoke\Blog\Dtos;

readonly class PostCreateData
{
    public function __construct(
        public string $title,
        public string $content,
        public int|string $authorId,
        public ?string $summary = null,
        public ?int $categoryId = null,
        public array $tagIds = [],
        public string $status = 'draft',
        public ?string $publishedAt = null,
        public bool $isPinned = false,
        public ?int $coverImageId = null,
        public string $sourceType = 'original',
        public ?string $sourceUrl = null,
        public string $language = 'en',
        public ?int $articleId = null,
    ) {
    }
}
