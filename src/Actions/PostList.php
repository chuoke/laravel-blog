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

        if ($data->search !== null && $data->search !== '') {
            $like = '%'.addcslashes($data->search, '\\%_').'%';
            $query->whereRaw("title LIKE ? ESCAPE '\\'", [$like]);
        }

        if ($data->categoryId !== null) {
            $query->where('category_id', $data->categoryId);
        }

        if ($data->status !== null) {
            $query->where('status', $data->status);
        }

        if ($data->language !== null) {
            $query->where('language', $data->language);
        }

        if ($data->pinnedOnly) {
            $query->where('is_pinned', true);
        }

        // By default, only show original posts (not translations)
        if ($data->originOnly === true || $data->originOnly === null) {
            $query->whereColumn('article_id', 'id');
        }

        $sortColumn = match ($data->sortBy) {
            'id', 'view_count', 'published_at', 'updated_at' => $data->sortBy,
            default => 'id',
        };

        $query->orderByDesc($sortColumn)->orderByDesc('id');

        return $query->paginate($data->perPage)->withQueryString();
    }
}
