<?php

namespace Chuoke\Blog\Http\Api\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Chuoke\Blog\Models\Attachment;
use Chuoke\Blog\Actions\AttachmentUpload;
use Chuoke\Blog\Actions\AttachmentGet;
use Chuoke\Blog\Actions\AttachmentList;
use Chuoke\Blog\Actions\AttachmentDelete;

class AttachmentController extends Controller
{
    public function index(Request $request, AttachmentList $action)
    {
        return $action->execute(
            perPage: (int) $request->get('per_page', 20),
            type: $request->get('type'),
        );
    }

    public function store(Request $request, AttachmentUpload $action)
    {
        $maxSize = config('blog.attachment.max_size', 10240);
        $allowedExtensions = implode(',', config('blog.attachment.allowed_extensions', []));

        $request->validate([
            'file' => "required|file|max:{$maxSize}|mimes:{$allowedExtensions}",
        ]);

        $attachment = $action->execute($request->file('file'));

        return response()->json($attachment, 201);
    }

    public function show(Attachment $attachment, AttachmentGet $action)
    {
        return $action->execute($attachment);
    }

    public function destroy(Attachment $attachment, AttachmentDelete $action)
    {
        $action->execute($attachment);

        return response()->noContent();
    }
}
