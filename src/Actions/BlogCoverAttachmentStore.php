<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Contracts\AttachmentPathGenerator;
use Chuoke\Blog\Dtos\AttachmentCreateData;
use Chuoke\Blog\Exceptions\BlogCoverProcessingUnavailable;
use Chuoke\Blog\Models\Attachment;
use Chuoke\Blog\Support\AttachmentPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class BlogCoverAttachmentStore
{
    private const int MaximumWidth = 1920;

    private const int MaximumPixels = 8_000_000;

    private const int MaximumSourceDimension = 8_000;

    public function __construct(
        private readonly AttachmentCreate $attachmentCreate,
        private readonly AttachmentPathGenerator $pathGenerator,
    ) {}

    public function fromUpload(UploadedFile $file): Attachment
    {
        return $this->fromContent(
            $file->getContent(),
            $file->getClientMimeType(),
            $file->getClientOriginalName(),
        );
    }

    public function fromContent(string $content, string $mimeType, string $fileName = 'ai-cover.png'): Attachment
    {
        [$content, $mimeType, $extension, $width, $height] = $this->optimize($content, $mimeType);
        $disk = config('blog.attachment.disk', 'public');
        $path = AttachmentPath::normalize($this->pathGenerator->generate($fileName, $extension));

        if (! Storage::disk($disk)->put($path, $content)) {
            throw new RuntimeException('Failed to store blog cover image.');
        }

        try {
            return $this->attachmentCreate->execute(new AttachmentCreateData(
                path: $path,
                fileName: pathinfo($fileName, PATHINFO_FILENAME).'.'.$extension,
                extension: $extension,
                mimeType: $mimeType,
                size: strlen($content),
                width: $width,
                height: $height,
                hash: hash('sha256', $content),
                disk: $disk,
            ));
        } catch (Throwable $e) {
            Storage::disk($disk)->delete($path);

            throw $e;
        }
    }

    /**
     * @return array{string, string, string, int, int}
     */
    private function optimize(string $content, string $mimeType): array
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            throw new BlogCoverProcessingUnavailable('Blog cover processing requires the GD extension with WebP support.');
        }

        $size = @getimagesizefromstring($content);

        if ($size === false) {
            throw new RuntimeException('Invalid blog cover image.');
        }

        [$width, $height] = $size;
        $mimeType = $size['mime'] ?? $mimeType;

        if ($width * $height > self::MaximumPixels || max($width, $height) > self::MaximumSourceDimension) {
            throw new RuntimeException('Blog cover image dimensions are too large.');
        }

        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException('Blog cover images must be JPEG, PNG, or WebP.');
        }

        $source = imagecreatefromstring($content);

        if ($source === false) {
            throw new RuntimeException('Unable to decode blog cover image.');
        }

        try {
            $targetWidth = min($width, self::MaximumWidth);
            $targetHeight = (int) round($height * ($targetWidth / $width));
            $resized = $targetWidth === $width ? $source : $this->resize($source, $targetWidth, $targetHeight);

            try {
                $original = $targetWidth === $width
                    ? [$content, $mimeType, $this->extensionFor($mimeType)]
                    : [$this->encode($resized, $mimeType), $mimeType, $this->extensionFor($mimeType)];
                $webp = [$this->encode($resized, 'image/webp'), 'image/webp', 'webp'];
                $best = strlen($webp[0]) < strlen($original[0]) ? $webp : $original;

                return [$best[0], $best[1], $best[2], $targetWidth, $targetHeight];
            } finally {
                if ($resized !== $source) {
                    imagedestroy($resized);
                }
            }
        } finally {
            imagedestroy($source);
        }
    }

    private function resize(\GdImage $source, int $width, int $height): \GdImage
    {
        $resized = imagecreatetruecolor($width, $height);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

        return $resized;
    }

    private function encode(\GdImage $image, string $mimeType): string
    {
        ob_start();

        $saved = match ($mimeType) {
            'image/jpeg' => imagejpeg($image, null, 85),
            'image/png' => imagepng($image, null, 6),
            'image/webp' => imagewebp($image, null, 82),
        };
        $content = ob_get_clean();

        if ($saved === false || $content === false) {
            throw new RuntimeException('Unable to encode blog cover image.');
        }

        return $content;
    }

    private function extensionFor(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        };
    }
}
