<?php

namespace Chuoke\Blog\Dtos;

readonly class TagUpdateData
{
    public function __construct(
        public ?array $name = null,
    ) {
    }
}
