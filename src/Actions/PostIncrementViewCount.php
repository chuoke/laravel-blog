<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Post;

class PostIncrementViewCount
{
    public function execute(Post $post, int $amount = 1): Post
    {
        $post->increment('view_count', $amount);

        return $post;
    }
}
