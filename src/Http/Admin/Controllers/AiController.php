<?php

namespace Chuoke\Blog\Http\Admin\Controllers;

use Chuoke\Blog\Contracts\BlogContentReviewer;
use Chuoke\Blog\Contracts\BlogCoverGenerator;
use Chuoke\Blog\Contracts\BlogSummaryGenerator;
use Chuoke\Blog\Exceptions\BlogAiUnavailable;
use Chuoke\Blog\Http\Admin\Requests\BlogAiContentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
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

    public function cover(BlogAiContentRequest $request, BlogCoverGenerator $generate): JsonResponse
    {
        try {
            return response()->json($generate->execute($request->contentData()), 201);
        } catch (BlogAiUnavailable $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to generate a blog cover. Please try again.'], 500);
        }
    }
}
