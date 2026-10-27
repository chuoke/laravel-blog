<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\CoverGeneration;
use Chuoke\Blog\Models\Post;
use Illuminate\Support\Facades\DB;

class PostDelete
{
    public function execute(Post $post): ?bool
    {
        $deleted = DB::transaction(function () use ($post): ?bool {
            Post::query()->whereKey($post)->lockForUpdate()->firstOrFail();
            $postIds = $post->isOriginal() ? $post->siblings()->pluck('id') : [$post->getKey()];

            if ($post->isOriginal()) {
                Post::query()->whereIn('id', $postIds)->orderBy('id')->lockForUpdate()->get(['id']);
            }

            CoverGeneration::query()
                ->whereIn('post_id', $postIds)
                ->where('is_active', true)
                ->update([
                    'status' => 'failed',
                    'is_active' => null,
                    'data' => null,
                    'failure_reason' => null,
                ]);

            return $post->isOriginal()
                ? $post->siblings()->delete()
                : $post->delete();
        });

        Blog::forgetFrontCache();

        return $deleted;
    }
}
