<?php

namespace Chuoke\Blog\Contracts;

interface AttachmentPathGenerator
{
    public function generate(string $fileName, string $extension, ?string $directory = null): string;
}
