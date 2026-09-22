<?php

namespace Chuoke\Blog\Services;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;
use Chuoke\Blog\Markdown\Extensions\AutoEmbedExtension;
use Chuoke\Blog\Markdown\Embeds\YoutubeDriver;
use Chuoke\Blog\Markdown\Embeds\BilibiliDriver;

class MarkdownRenderer
{
    public function render(string $markdown): string
    {
        // 'strip' by default: raw HTML in Markdown is a stored-XSS vector unless
        // every author is fully trusted. Only set 'blog.markdown.html_input' to
        // 'allow' if you understand and accept that risk.
        $config = [
            'html_input' => config('blog.markdown.html_input', 'strip'), // 'allow', 'strip', or 'escape'
            'allow_unsafe_links' => false,
        ];

        // Configure the Environment with all the CommonMark parsers/renderers
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        
        // Add common extensions
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new TaskListExtension());

        // Add our custom AutoEmbed extension
        $drivers = [
            new YoutubeDriver(),
            new BilibiliDriver(),
        ];
        
        $environment->addExtension(new AutoEmbedExtension($drivers));

        // Instantiate the converter engine and turn it into HTML
        $converter = new MarkdownConverter($environment);

        return $converter->convert($markdown)->getContent();
    }
}
