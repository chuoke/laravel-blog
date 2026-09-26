<?php

namespace Chuoke\Blog\Support;

readonly class BlogLocale
{
    public function __construct(
        public string $code,
        public string $label,
    ) {
    }

    public static function from(string $code): self
    {
        return new self(
            code: $code,
            label: config("blog.supported_locales.{$code}", $code),
        );
    }
}
