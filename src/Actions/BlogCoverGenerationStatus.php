<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\CoverGeneration;

class BlogCoverGenerationStatus
{
    public function execute(CoverGeneration $coverGeneration): CoverGeneration
    {
        CoverGeneration::query()
            ->whereKey($coverGeneration)
            ->where('is_active', true)
            ->whereIn('status', ['pending', 'processing'])
            ->where('updated_at', '<=', now()->subSeconds((int) config('blog.ai.cover.retry_after', 3600)))
            ->update([
                'status' => 'failed',
                'is_active' => null,
                'data' => null,
                'failure_reason' => $coverGeneration->status === 'processing' ? 'timeout' : 'queue_timeout',
            ]);

        return $coverGeneration->refresh();
    }
}
