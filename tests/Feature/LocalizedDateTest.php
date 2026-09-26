<?php

use Carbon\Carbon;
use Chuoke\Blog\BlogManager;

it('formats dates with the blog locale by default', function () {
    config(['blog.locale' => 'zh_CN']);

    $formatted = (new BlogManager())->formatDate(Carbon::create(2026, 9, 24), 'long');

    expect($formatted)
        ->toContain('年')
        ->toContain('月')
        ->toContain('日');
});

it('formats dates with an article locale when one is provided', function () {
    $formatted = (new BlogManager())->formatDate(Carbon::create(2026, 9, 24), 'long', 'en');

    expect($formatted)->toBe('September 24, 2026');
});
