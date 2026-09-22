<?php

namespace Chuoke\Blog\Markdown\Embeds;

use Chuoke\Blog\Contracts\EmbedDriver;

class YoutubeDriver implements EmbedDriver
{
    public function matches(string $url): bool
    {
        return (bool) preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url);
    }

    public function render(string $url): string
    {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);
        $videoId = $matches[1] ?? null;

        if (!$videoId) {
            return '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a>';
        }

        return '<div class="blog-embed-youtube" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%;">
            <iframe src="https://www.youtube.com/embed/' . $videoId . '" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>';
    }
}
