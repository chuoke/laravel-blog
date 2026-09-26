<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\Post;

class PostArchive
{
    public function execute(Post $post): Post
    {
        $post->update(['status' => 'archived']);

        Blog::forgetFrontCache();

        return $post;
    }
}
