<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Post;
use Chuoke\Blog\Dtos\PostListData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostList
{
    public function execute(PostListData $data): LengthAwarePaginator
    {
        $query = Post::with(['category', 'tags', 'author', 'coverImage']);

        if ($data->categoryId !== null) {
            $query->where('category_id', $data->categoryId);
        }

        if ($data->status !== null) {
            $query->where('status', $data->status);
        }

        if ($data->language !== null) {
            $query->where('language', $data->language);
        }

        // By default, only show original posts (not translations)
        if ($data->originOnly === true || $data->originOnly === null) {
            $query->whereColumn('article_id', 'id');
        }

        $query->latest('id');

        return $query->paginate($data->perPage);
    }
}
