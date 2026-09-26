<?php

use Inertia\Inertia;

it('shares configured blog route prefixes with Inertia pages', function () {
    config([
        'blog.admin_route_prefix' => 'editor/articles',
        'blog.api_route_prefix' => 'api/editor/articles',
    ]);

    $routes = Inertia::getShared('blog.routes');

    expect($routes())->toBe([
        'admin' => '/editor/articles',
        'api' => '/api/editor/articles',
    ]);
});
