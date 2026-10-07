<?php

namespace Chuoke\Blog\Events;

use Chuoke\Blog\Models\CoverGeneration;

class BlogCoverGenerationFailed
{
    public function __construct(
        public readonly CoverGeneration $coverGeneration,
    ) {
    }
}
