<?php

namespace Chuoke\Blog\Jobs;

use Chuoke\Blog\Contracts\BlogCoverGenerator;
use Chuoke\Blog\Models\CoverGeneration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class BlogCoverGenerateJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(
        public readonly int $coverGenerationId,
    ) {
    }

    public function handle(BlogCoverGenerator $generate): void
    {
        $claimed = CoverGeneration::query()
            ->whereKey($this->coverGenerationId)
            ->where('is_active', true)
            ->where('status', 'pending')
            ->update(['status' => 'processing']);

        if ($claimed === 0) {
            return;
        }

        $coverGeneration = CoverGeneration::query()->find($this->coverGenerationId);

        if ($coverGeneration === null) {
            return;
        }

        $attachment = $generate->execute($coverGeneration->data);

        CoverGeneration::query()
            ->whereKey($this->coverGenerationId)
            ->where('is_active', true)
            ->where('status', 'processing')
            ->update([
            'status' => 'completed',
            'is_active' => null,
            'attachment_id' => $attachment->getKey(),
            'data' => null,
            ]);
    }

    public function failed(Throwable $exception): void
    {
        CoverGeneration::query()
            ->whereKey($this->coverGenerationId)
            ->where('is_active', true)
            ->where('status', 'processing')
            ->update([
                'status' => 'failed',
                'is_active' => null,
                'data' => null,
            ]);
    }
}
