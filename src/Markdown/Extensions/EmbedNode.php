<?php

namespace Chuoke\Blog\Markdown\Extensions;

use League\CommonMark\Node\Block\AbstractBlock;

/**
 * A block node representing a trusted, package-generated embed (e.g. a
 * YouTube/Bilibili iframe). Its HTML is produced entirely by our own
 * EmbedDriver implementations, never from raw author-supplied HTML, so it
 * is rendered unconditionally by EmbedRenderer regardless of the
 * 'html_input' Markdown configuration used to guard against untrusted
 * raw HTML in post content.
 */
class EmbedNode extends AbstractBlock
{
    public function __construct(private readonly string $html)
    {
        parent::__construct();
    }

    public function getHtml(): string
    {
        return $this->html;
    }
}
