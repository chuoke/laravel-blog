<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Services\MarkdownRenderer;

class RenderMarkdown
{
    public function execute(string $markdown): string
    {
        $renderer = new MarkdownRenderer();
        return $renderer->render($markdown);
    }
}
