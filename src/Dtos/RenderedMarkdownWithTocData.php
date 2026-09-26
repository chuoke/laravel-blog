<?php

namespace Chuoke\Blog\Dtos;

readonly class RenderedMarkdownWithTocData
{
    /**
     * @param array<int, array{id: string, text: string, level: int}> $tableOfContents
     */
    public function __construct(
        public string $html,
        public array $tableOfContents,
    ) {
    }
}
