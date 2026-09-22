<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Attachment;

class AttachmentGet
{
    public function execute(Attachment $attachment): Attachment
    {
        return $attachment;
    }
}
