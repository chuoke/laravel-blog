<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Contracts\AttachmentPathGenerator;
use Chuoke\Blog\Dtos\AttachmentCreateData;
use Chuoke\Blog\Models\Attachment;
use Chuoke\Blog\Support\AttachmentPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttachmentUpload
{
    public function execute(UploadedFile $file, ?string $directory = null, ?string $disk = null): Attachment
    {
        $disk = $disk ?? config('blog.attachment.disk', 'public');
        $path = AttachmentPath::normalize(app(AttachmentPathGenerator::class)->generate(
            $file->getClientOriginalName(),
            $file->getClientOriginalExtension(),
            $directory,
        ));

        $storedPath = Storage::disk($disk)->putFileAs(dirname($path) === '.' ? '' : dirname($path), $file, basename($path));

        if ($storedPath === false) {
            throw new \RuntimeException('Failed to store blog attachment.');
        }

        $width = null;
        $height = null;

        if (str_starts_with($file->getMimeType(), 'image/')) {
            $imageSize = @getimagesize($file->getRealPath());
            if ($imageSize) {
                $width = $imageSize[0];
                $height = $imageSize[1];
            }
        }

        $data = new AttachmentCreateData(
            path: $storedPath,
            fileName: $file->getClientOriginalName(),
            extension: $file->getClientOriginalExtension(),
            type: $this->resolveType($file->getMimeType()),
            mimeType: $file->getMimeType(),
            size: $file->getSize(),
            width: $width,
            height: $height,
            hash: hash_file('sha256', $file->getRealPath()),
            disk: $disk,
        );

        return app(AttachmentCreate::class)->execute($data);
    }

    protected function resolveType(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => 'image',
            str_starts_with($mimeType, 'video/') => 'video',
            str_starts_with($mimeType, 'audio/') => 'audio',
            default => 'document',
        };
    }
}
