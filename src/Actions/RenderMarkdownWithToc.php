<?php

namespace Chuoke\Blog\Actions;

use DOMDocument;
use DOMXPath;
use Chuoke\Blog\Dtos\RenderedMarkdownWithTocData;
use Chuoke\Blog\Services\MarkdownRenderer;

class RenderMarkdownWithToc
{
    public function execute(string $markdown): RenderedMarkdownWithTocData
    {
        $html = (new MarkdownRenderer())->render($markdown, withHeadingIds: true);

        return new RenderedMarkdownWithTocData($html, $this->extractTableOfContents($html));
    }

    /**
     * @return array<int, array{id: string, text: string, level: int}>
     */
    private function extractTableOfContents(string $html): array
    {
        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $items = [];

        foreach ((new DOMXPath($document))->query('//h2[@id] | //h3[@id]') as $heading) {
            $items[] = [
                'id' => $heading->getAttribute('id'),
                'text' => trim($heading->textContent),
                'level' => (int) substr($heading->nodeName, 1),
            ];
        }

        return $items;
    }
}
