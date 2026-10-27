<?php

namespace Chuoke\Blog\Http\Admin\Controllers;

use Chuoke\Blog\Actions\BlogCoverGenerationStart;
use Chuoke\Blog\Actions\BlogCoverGenerationStatus;
use Chuoke\Blog\Contracts\BlogContentReviewer;
use Chuoke\Blog\Contracts\BlogContentTranslator;
use Chuoke\Blog\Contracts\BlogSlugGenerator;
use Chuoke\Blog\Contracts\BlogSummaryGenerator;
use Chuoke\Blog\Exceptions\BlogAiUnavailable;
use Chuoke\Blog\Http\Admin\Requests\BlogAiContentRequest;
use Chuoke\Blog\Models\CoverGeneration;
use Chuoke\Blog\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    public function cover(BlogAiContentRequest $request, BlogCoverGenerationStart $start): JsonResponse
    {
        $validated = $request->validate([
            'post_id' => ['required', 'integer', Rule::exists('blog_posts', 'id')->whereNull('deleted_at')],
            'retry' => ['sometimes', 'boolean'],
        ]);

        try {
            $coverGeneration = $start->execute(
                (int) $validated['post_id'],
                (string) $request->user()->getKey(),
                $request->contentData(),
                (bool) ($validated['retry'] ?? false),
            );

            if ($coverGeneration === null) {
                return response()->json(['message' => 'A blog cover is already being generated.'], 409);
            }

            return response()->json([
                'id' => $coverGeneration->id,
                'status' => $coverGeneration->status,
            ], 202);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Post not found.'], 404);
        } catch (BlogAiUnavailable $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to generate a blog cover. Please try again.'], 500);
        }
    }

    public function coverStatus(Request $request, CoverGeneration $coverGeneration, BlogCoverGenerationStatus $status): JsonResponse
    {
        abort_unless($coverGeneration->user_id === (string) $request->user()->getKey(), 404);

        $coverGeneration = $status->execute($coverGeneration);

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
