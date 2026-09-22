<?php

namespace Chuoke\Blog\Dtos;

readonly class CategoryCreateData
{
    public function __construct(
        public array $name,
        public ?array $description = null,
        public ?int $parentId = null,
        public int $sortOrder = 0,
    ) {
    }
}
