<?php

namespace Chuoke\Blog\Markdown\Extensions;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Block\Paragraph;
use Chuoke\Blog\Contracts\EmbedDriver;

class AutoEmbedExtension implements ExtensionInterface
{
    /** @var EmbedDriver[] */
    protected array $drivers = [];

    public function __construct(array $drivers = [])
    {
        $this->drivers = $drivers;
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentParsedEvent::class, [$this, 'onDocumentParsed']);
        $environment->addRenderer(EmbedNode::class, new EmbedRenderer());
    }

    public function onDocumentParsed(DocumentParsedEvent $event): void
    {
        $document = $event->getDocument();
        $walker = $document->walker();

        while ($event = $walker->next()) {
            $node = $event->getNode();

            // We only care about Links, and we process them when entering
            if (!$node instanceof Link || !$event->isEntering()) {
                continue;
            }

            // Check if this link is a standalone link in a paragraph
            $parent = $node->parent();
            if (!$parent instanceof Paragraph) {
                continue;
            }

            $url = $node->getUrl();

            // Check if it's a naked link/only child of the paragraph
            // CommonMark wraps naked URLs in a Link node, but there might be a Text node inside it.
            // For paragraph, the Link should be the ONLY child.
            if ($parent->firstChild() !== $node || $parent->lastChild() !== $node) {
                continue;
            }

            foreach ($this->drivers as $driver) {
                if ($driver->matches($url)) {
                    $html = $driver->render($url);

                    // Use our own EmbedNode/EmbedRenderer (not a raw HtmlBlock) so this
                    // trusted, driver-generated markup renders regardless of the
                    // 'html_input' setting used to strip/escape untrusted author HTML.
                    $parent->replaceWith(new EmbedNode($html));
                    break;
                }
            }
        }
    }
}
