<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\Post;

class PostDelete
{
    public function execute(Post $post): ?bool
    {
        $deleted = $post->delete();

        Blog::forgetFrontCache();

        return $deleted;
    }
}
