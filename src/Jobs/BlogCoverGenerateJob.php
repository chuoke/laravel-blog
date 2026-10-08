<?php

namespace Chuoke\Blog\Jobs;

use Chuoke\Blog\Contracts\BlogCoverGenerator;
use Chuoke\Blog\Events\BlogCoverGenerated;
use Chuoke\Blog\Events\BlogCoverGenerationFailed;
use Chuoke\Blog\Models\CoverGeneration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class BlogCoverGenerateJob implements ShouldQueue
{
    use Queueable;

    public int $tries;

    public int $timeout;

    public function __construct(
        public readonly int $coverGenerationId,
    ) {
        $this->onQueue('ai-image');
        $this->tries = (int) config('blog.ai.cover.tries', 2);
        $this->timeout = (int) config('blog.ai.cover.timeout', 3300);
    }

    public function handle(BlogCoverGenerator $generate): void
    {
        $staleAt = now()->subSeconds((int) config('blog.ai.cover.retry_after', 3600));

        $claimed = CoverGeneration::query()
            ->whereKey($this->coverGenerationId)
            ->where('is_active', true)
            ->where(function ($query) use ($staleAt): void {
                $query->where('status', 'pending')
                    ->orWhere(function ($query) use ($staleAt): void {
                        $query->where('status', 'processing')
                            ->where('updated_at', '<=', $staleAt);
                    });
            })
            ->update(['status' => 'processing']);

        if ($claimed === 0) {
            return;
        }

        $coverGeneration = CoverGeneration::query()->find($this->coverGenerationId);

        if ($coverGeneration === null) {
            return;
        }

        try {
            $attachment = $generate->execute([
                ...($coverGeneration->data ?? []),
                'cover_generation_id' => $coverGeneration->getKey(),
            ]);
        } catch (Throwable $exception) {
            CoverGeneration::query()
                ->whereKey($this->coverGenerationId)
                ->where('is_active', true)
                ->where('status', 'processing')
                ->update(['status' => 'pending']);

            throw $exception;
        }

        $completed = CoverGeneration::query()
            ->whereKey($this->coverGenerationId)
            ->whereIn('status', ['processing', 'failed'])
            ->update([
                'status' => 'completed',
                'is_active' => null,
                'attachment_id' => $attachment->getKey(),
                'data' => null,
                'failure_reason' => null,
            ]);

        if ($completed !== 0) {
            try {
                event(new BlogCoverGenerated($coverGeneration->refresh()));
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Blog cover generation failed.', [
            'cover_generation_id' => $this->coverGenerationId,
            'exception' => $exception,
        ]);

        $staleAt = now()->subSeconds((int) config('blog.ai.cover.retry_after', 3600));

        $failed = CoverGeneration::query()
            ->whereKey($this->coverGenerationId)
            ->where('is_active', true)
            ->where(function ($query) use ($staleAt): void {
                $query->where('status', 'pending')
                    ->orWhere(function ($query) use ($staleAt): void {
                        $query->where('status', 'processing')
                            ->where('updated_at', '<=', $staleAt);
                    });
            })
            ->update([
                'status' => 'failed',
                'is_active' => null,
                'data' => null,
                'failure_reason' => $this->failureReason($exception),
            ]);

        if ($failed !== 0) {
            $coverGeneration = CoverGeneration::query()->find($this->coverGenerationId);

            if ($coverGeneration !== null) {
                try {
                    event(new BlogCoverGenerationFailed($coverGeneration));
                } catch (Throwable $eventException) {
                    report($eventException);
                }
            }
        }
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60];
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
