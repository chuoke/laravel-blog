<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Tag;

class TagGet
{
    public function execute(Tag $tag): Tag
    {
        return $tag;
    }
}
