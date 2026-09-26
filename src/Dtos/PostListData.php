<?php

namespace Chuoke\Blog\Dtos;

readonly class PostListData
{
    public function __construct(
        public int $perPage = 15,
        public ?string $search = null,
        public ?int $categoryId = null,
        public ?string $status = null,
        public ?string $language = null,
        public ?bool $originOnly = null,
        public bool $pinnedOnly = false,
        public string $sortBy = 'id',
    ) {
    }
}
