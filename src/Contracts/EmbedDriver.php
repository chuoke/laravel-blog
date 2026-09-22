<?php

namespace Chuoke\Blog\Contracts;

interface EmbedDriver
{
    /**
     * Determine if the driver can handle the given URL.
     */
    public function matches(string $url): bool;

    /**
     * Render the embed HTML for the URL.
     */
    public function render(string $url): string;
}
