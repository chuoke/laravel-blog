<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\Post;

class PostTogglePin
{
    public function execute(Post $post): Post
    {
        $post->update(['is_pinned' => ! $post->is_pinned]);

        // Sync to all translations
        if ($post->article_id) {
            Post::where('article_id', $post->article_id)
                ->where('id', '!=', $post->id)
                ->update(['is_pinned' => $post->is_pinned]);
        }

        Blog::forgetFrontCache();

        return $post;
    }
}
