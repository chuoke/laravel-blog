<?php

namespace Chuoke\Blog\Jobs;

use Chuoke\Blog\Contracts\BlogCoverGenerator;
use Chuoke\Blog\Models\CoverGeneration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class BlogCoverGenerateJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 0;

    public function __construct(
        public readonly int $coverGenerationId,
    ) {
        $this->onQueue('ai-image');
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
        Log::error('Blog cover generation failed.', [
            'cover_generation_id' => $this->coverGenerationId,
            'exception' => $exception,
        ]);

        CoverGeneration::query()
            ->whereKey($this->coverGenerationId)
            ->where('is_active', true)
            ->where('status', 'processing')
            ->update([
                'status' => 'failed',
                'is_active' => null,
                'data' => null,
                'failure_reason' => $this->failureReason($exception),
            ]);
    }

    private function failureReason(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'timeout') || str_contains($message, 'timed out')) {
            return 'timeout';
        }

        if (preg_match('/\\b(400|401|403|422)\\b/', $message) === 1) {
            return 'rejected';
        }

        return 'unavailable';
    }
}
