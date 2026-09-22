<?php

namespace Chuoke\Blog\Support;

class ThemeAssets
{
    /**
     * Read a theme's precompiled CSS bundle (built via `npm run build:theme`
     * from resources/css/themes/{theme}/source.css) so it can be inlined
     * directly into the layout. Inlining avoids requiring host apps to run
     * any publish/build step to get styled output - installing the package
     * is enough.
     */
    public static function css(string $theme): string
    {
        $path = __DIR__."/../../resources/css/blog/{$theme}-theme.css";

        return is_file($path) ? file_get_contents($path) : '';
    }
}
