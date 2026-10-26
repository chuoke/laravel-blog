<?php

namespace Chuoke\Blog\Http\Admin\Controllers;

use Chuoke\Blog\Contracts\BlogContentReviewer;
use Chuoke\Blog\Contracts\BlogContentTranslator;
use Chuoke\Blog\Contracts\BlogSlugGenerator;
use Chuoke\Blog\Contracts\BlogSummaryGenerator;
use Chuoke\Blog\Exceptions\BlogAiUnavailable;
use Chuoke\Blog\Http\Admin\Requests\BlogAiContentRequest;
use Chuoke\Blog\Jobs\BlogCoverGenerateJob;
use Chuoke\Blog\Models\CoverGeneration;
use Chuoke\Blog\Models\Post;
use Chuoke\Blog\Support\BlogAi;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Throwable;

class AiController extends Controller
{
    public function summary(BlogAiContentRequest $request, BlogSummaryGenerator $generate): JsonResponse
    {
        try {
            return response()->json(['summary' => $generate->execute($request->contentData())]);
        } catch (BlogAiUnavailable $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to generate a blog summary. Please try again.'], 500);
        }
    }

    public function slug(BlogAiContentRequest $request, BlogSlugGenerator $generate): JsonResponse
    {
        try {
            return response()->json(['slug' => $generate->execute($request->contentData())]);
        } catch (BlogAiUnavailable $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to generate a blog slug. Please try again.'], 500);
        }
    }

    public function review(BlogAiContentRequest $request, BlogContentReviewer $review): JsonResponse
    {
        try {
            return response()->json($review->execute($request->contentData()));
        } catch (BlogAiUnavailable $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to review this blog content. Please try again.'], 500);
        }
    }

    public function cover(BlogAiContentRequest $request): JsonResponse
    {
        $validated = $request->validate([
            'post_id' => ['required', 'integer', Rule::exists('blog_posts', 'id')->whereNull('deleted_at')],
            'retry' => ['sometimes', 'boolean'],
        ]);

        try {
            BlogAi::ensureAvailable();

            $data = $request->contentData();
            $postId = (int) $validated['post_id'];
            $coverGeneration = CoverGeneration::query()
                ->where('post_id', $postId)
                ->where('is_active', true)
                ->first();

            if ($coverGeneration !== null) {
                $staleAt = now()->subSeconds((int) config('blog.ai.cover.retry_after', 3600));
                $stale = $coverGeneration->updated_at->lte($staleAt);
                $retryPending = ($validated['retry'] ?? false)
                    && $coverGeneration->status === 'pending'
                    && $coverGeneration->user_id === (string) $request->user()->getKey();

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

            if ($coverGeneration !== null && $coverGeneration->user_id !== (string) $request->user()->getKey()) {
                return response()->json(['message' => 'A blog cover is already being generated.'], 409);
            }

            $shouldDispatch = false;

            if ($coverGeneration === null) {
                try {
                    $coverGeneration = CoverGeneration::query()->create([
                        'user_id' => $request->user()->getKey(),
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

                    if ($coverGeneration->user_id !== (string) $request->user()->getKey()) {
                        return response()->json(['message' => 'A blog cover is already being generated.'], 409);
                    }
                }

            }

            if ($shouldDispatch) {
                BlogCoverGenerateJob::dispatch($coverGeneration->id)->afterCommit();
            }

            return response()->json([
                'id' => $coverGeneration->id,
                'status' => $coverGeneration->status,
            ], 202);
        } catch (BlogAiUnavailable $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to generate a blog cover. Please try again.'], 500);
        }
    }

    public function coverStatus(Request $request, CoverGeneration $coverGeneration): JsonResponse
    {
        abort_unless($coverGeneration->user_id === (string) $request->user()->getKey(), 404);

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
        $coverGeneration->refresh();

        return response()->json([
            'status' => $coverGeneration->status,
            'attachment' => $coverGeneration->attachment,
            'reason' => $coverGeneration->status === 'failed' ? $coverGeneration->failure_reason : null,
        ]);
    }

    public function translate(Post $post, BlogContentTranslator $translate): JsonResponse
    {
        abort_unless($post->isTranslation(), 422);

        $original = Post::query()->findOrFail($post->article_id);

        try {
            return response()->json($translate->execute([
                'title' => $original->title,
                'summary' => $original->summary,
                'content' => $original->content,
                'sourceLanguage' => $original->language,
                'targetLanguage' => $post->language,
            ]));
        } catch (BlogAiUnavailable $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to translate this blog content. Please try again.'], 500);
        }
    }
}
