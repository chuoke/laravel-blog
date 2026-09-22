<?php

namespace Chuoke\Blog\Dtos;

readonly class CategoryUpdateData
{
    public function __construct(
        public ?array $name = null,
        public ?array $description = null,
        public ?int $parentId = null,
        public ?int $sortOrder = null,
    ) {
    }
}
