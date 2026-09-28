<?php

use Chuoke\Blog\Http\Admin\Middleware\ShareBlogInertiaRoutes;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

it('shares configured blog route prefixes for each admin request', function () {
    config([
        'blog.admin_route_prefix' => 'editor/articles',
        'blog.api_route_prefix' => 'api/editor/articles',
    ]);

    Inertia::flushShared();

    app(ShareBlogInertiaRoutes::class)->handle(Request::create('/editor/articles'), fn () => new Response);

    $routes = Inertia::getShared('blog.routes');

    expect($routes)->toBe([
        'admin' => '/editor/articles',
        'api' => '/api/editor/articles',
    ]);
});
