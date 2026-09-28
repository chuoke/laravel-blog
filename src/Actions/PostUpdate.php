<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Dtos\PostUpdateData;
use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\Post;
use Illuminate\Support\Facades\DB;

class PostUpdate
{
    public function execute(Post $post, PostUpdateData $data): Post
    {
        $updatedPost = DB::transaction(function () use ($data, $post): Post {
            if ($post->isOriginal() && $data->language !== null && $data->language !== $post->language) {
                Post::onlyTrashed()
                    ->where('article_id', $post->article_id)
                    ->where('language', $data->language)
                    ->forceDelete();
            }

            $updateAttributes = array_filter([
                'title' => $data->title,
                'summary' => $data->summary,
                'content' => $data->content,
                'category_id' => $data->categoryId,
                'cover_image_id' => $data->coverImageId,
                'language' => $data->language,
                'source_type' => $data->sourceType,
                'source_url' => $data->sourceUrl,
            ], fn ($value) => $value !== null);

            if ($post->isTranslation()) {
                unset($updateAttributes['category_id']);
            }

            if (! empty($updateAttributes)) {
                $post->update($updateAttributes);

                // Sync structural fields to all sibling translations
                if ($post->isOriginal()) {
                    $structuralFields = array_intersect_key($updateAttributes, array_flip([
                        'category_id',
                    ]));

                    if (! empty($structuralFields)) {
                        $post->translations()->where('id', '!=', $post->id)->update($structuralFields);
                    }
                }
            }

            if ($data->tagIds !== null && ! $post->isTranslation()) {
                $post->tags()->sync($data->tagIds);
            }

            if ($data->status === 'published') {
                $post->update([
                    'status' => 'published',
                    'published_at' => $post->published_at ?? now(),
                ]);
            }

            return $post->fresh();
        });

        Blog::forgetFrontCache();

        return $updatedPost;
    }
}
