<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Post;

class PostPublish
{
    public function execute(Post $post): Post
    {
        $post->update([
            'status' => 'published',
            'published_at' => $post->published_at ?? now(),
        ]);

        return $post;
    }
}
