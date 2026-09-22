<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Tag;

class TagDelete
{
    public function execute(Tag $tag): ?bool
    {
        return $tag->delete();
    }
}
