<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\Post;
use Illuminate\Support\Str;

class PostCreate
{
    public function execute(PostCreateData $data)
    {
        // 1. Generate uid using the configured generator
        $uid = app(UidGenerate::class)->execute();

        $slug = $data->slug ?? Str::slug($data->title);

        if ($data->slug === null) {
            $slug = ($slug ?: 'post').'-'.strtolower(Str::random(5));
        }

        $post = Post::create([
            'uid' => $uid,
            'author_id' => $data->authorId,
            'category_id' => $data->categoryId,
            'article_id' => $data->articleId,
            'title' => $data->title,
            'slug' => $slug,
            'summary' => $data->summary,
            'content' => $data->content,
            'status' => $data->status,
            'published_at' => $data->publishedAt,
            'is_pinned' => $data->isPinned,
            'cover_image_id' => $data->coverImageId,
            'source_type' => $data->sourceType,
            'source_url' => $data->sourceUrl,
            'language' => $data->language,
        ]);

        // If this is the original post, set its article_id to its own id
        if ($post->article_id === null) {
            $post->update(['article_id' => $post->id]);
        }

        if (! empty($data->tagIds)) {
            $post->tags()->sync($data->tagIds);
        }

        Blog::forgetFrontCache();

        return $post;
    }
}
