<?php

namespace Chuoke\Blog\Markdown\Extensions;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

class EmbedRenderer implements NodeRendererInterface
{
    /**
     * @param EmbedNode $node
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        EmbedNode::assertInstanceOf($node);

        return $node->getHtml();
    }
}
