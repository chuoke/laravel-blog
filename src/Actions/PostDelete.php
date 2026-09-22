<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Post;

class PostDelete
{
    public function execute(Post $post): ?bool
    {
        return $post->delete();
    }
}
