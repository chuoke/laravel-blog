<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Jobs\BlogCoverGenerateJob;
use Chuoke\Blog\Models\CoverGeneration;
use Chuoke\Blog\Support\BlogAi;
use Illuminate\Database\UniqueConstraintViolationException;

class BlogCoverGenerationStart
{
    public function execute(int $postId, string $userId, array $data, bool $retry): ?CoverGeneration
    {
        BlogAi::ensureAvailable();

        $coverGeneration = CoverGeneration::query()
            ->where('post_id', $postId)
            ->where('is_active', true)
            ->first();

        if ($coverGeneration !== null) {
            $staleAt = now()->subSeconds((int) config('blog.ai.cover.retry_after', 3600));
            $stale = $coverGeneration->updated_at->lte($staleAt);
            $retryPending = $retry
                && $coverGeneration->status === 'pending'
                && $coverGeneration->user_id === $userId;

            if ($stale || $retryPending) {
                $expired = CoverGeneration::query()
                    ->whereKey($coverGeneration)
                    ->where('is_active', true)
                    ->where('status', $coverGeneration->status)
                    ->when($stale, fn ($query) => $query->where('updated_at', '<=', $staleAt))
                    ->update([
                        'status' => 'failed',
                        'is_active' => null,
                        'data' => null,
                        'failure_reason' => $stale
                            ? ($coverGeneration->status === 'processing' ? 'timeout' : 'queue_timeout')
                            : null,
                    ]);

                $coverGeneration = $expired ? null : CoverGeneration::query()
                    ->where('post_id', $postId)
                    ->where('is_active', true)
                    ->first();
            }
        }

        if ($coverGeneration !== null && $coverGeneration->user_id !== $userId) {
            return null;
        }

        $shouldDispatch = false;

        if ($coverGeneration === null) {
            try {
                $coverGeneration = CoverGeneration::query()->create([
                    'user_id' => $userId,
                    'post_id' => $postId,
                    'status' => 'pending',
                    'data' => $data,
                ]);
                $shouldDispatch = true;
            } catch (UniqueConstraintViolationException) {
                $coverGeneration = CoverGeneration::query()
                    ->where('post_id', $postId)
                    ->where('is_active', true)
                    ->firstOrFail();

                if ($coverGeneration->user_id !== $userId) {
                    return null;
                }
            }
        }

        if ($shouldDispatch) {
            BlogCoverGenerateJob::dispatch($coverGeneration->id)->afterCommit();
        }

        return $coverGeneration;
    }
}
