<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentDelete
{
    public function execute(Attachment $attachment, bool $deleteFile = true): ?bool
    {
        if ($deleteFile) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        return $attachment->delete();
    }
}
