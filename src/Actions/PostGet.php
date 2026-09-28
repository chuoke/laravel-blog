<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Post;

class PostGet
{
    public function execute(Post $post): Post
    {
        return $post->load(['category', 'tags', 'author', 'coverImage']);
    }
}
