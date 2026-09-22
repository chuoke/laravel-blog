<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Dtos\AttachmentCreateData;
use Chuoke\Blog\Models\Attachment;

class AttachmentCreate
{
    public function execute(AttachmentCreateData $data): Attachment
    {
        return Attachment::create([
            'disk' => $data->disk,
            'path' => $data->path,
            'file_name' => $data->fileName,
            'extension' => $data->extension,
            'type' => $data->type,
            'mime_type' => $data->mimeType,
            'size' => $data->size,
            'width' => $data->width,
            'height' => $data->height,
            'alt' => $data->alt,
            'hash' => $data->hash,
        ]);
    }
}
