<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Dtos\PostUpdateData;
use Chuoke\Blog\Models\Post;

class PostUpdate
{
    public function execute(Post $post, PostUpdateData $data): Post
    {
        $updateAttributes = array_filter([
            'title' => $data->title,
            'summary' => $data->summary,
            'content' => $data->content,
            'category_id' => $data->categoryId,
            'cover_image_id' => $data->coverImageId,
            'source_type' => $data->sourceType,
            'source_url' => $data->sourceUrl,
        ], fn ($value) => $value !== null);

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

        if ($data->tagIds !== null) {
            $post->tags()->sync($data->tagIds);
        }

        return $post->fresh();
    }
}
