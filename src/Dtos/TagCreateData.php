<?php

namespace Chuoke\Blog\Dtos;

readonly class TagCreateData
{
    public function __construct(
        public array $name,
    ) {
    }
}
