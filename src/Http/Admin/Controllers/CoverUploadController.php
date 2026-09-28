<?php

namespace Chuoke\Blog\Http\Admin\Controllers;

use Chuoke\Blog\Actions\BlogCoverAttachmentStore;
use Chuoke\Blog\Exceptions\BlogCoverProcessingUnavailable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

class CoverUploadController extends Controller
{
    public function __invoke(Request $request, BlogCoverAttachmentStore $store): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:'.config('blog.attachment.max_size', 10240), 'mimes:jpg,jpeg,png,webp'],
        ]);

        try {
            return response()->json($store->fromUpload($request->file('file')), 201);
        } catch (BlogCoverProcessingUnavailable $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to process the blog cover image. Please try again.'], 500);
        }
    }
}
