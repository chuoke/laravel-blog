<?php

namespace Chuoke\Blog\Markdown\Embeds;

use Chuoke\Blog\Contracts\EmbedDriver;

class BilibiliDriver implements EmbedDriver
{
    public function matches(string $url): bool
    {
        return str_contains($url, 'bilibili.com/video/');
    }

    public function render(string $url): string
    {
        preg_match('/bilibili\.com\/video\/([A-Za-z0-9]+)/i', $url, $matches);
        $bvid = $matches[1] ?? null;

        if (!$bvid) {
            return '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a>';
        }

        return '<div class="blog-embed-bilibili" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%;">
            <iframe src="//player.bilibili.com/player.html?bvid=' . $bvid . '&page=1&high_quality=1&danmaku=0" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" scrolling="no" border="0" frameborder="no" framespacing="0" allowfullscreen="true"></iframe>
        </div>';
    }
}
