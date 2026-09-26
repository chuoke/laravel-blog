<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\Tag;

class TagDelete
{
    public function execute(Tag $tag): ?bool
    {
        $deleted = $tag->delete();

        Blog::forgetFrontCache();

        return $deleted;
    }
}
