<?php

use Chuoke\Blog\Actions\RenderMarkdown;

it('can render basic markdown', function () {
    $action = new RenderMarkdown();
    $markdown = "# Hello World\n\nThis is a **test**.";
    
    $html = $action->execute($markdown);
    
    expect($html)->toContain('<h1>Hello World</h1>')
                 ->toContain('<strong>test</strong>');
});

it('can auto embed youtube links', function () {
    $action = new RenderMarkdown();
    $markdown = "Check out this video:\n\nhttps://www.youtube.com/watch?v=dQw4w9WgXcQ\n\nAwesome, right?";
    
    $html = $action->execute($markdown);
    
    expect($html)->toContain('Check out this video:')
                 ->toContain('Awesome, right?')
                 ->toContain('iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"');
                 
    expect($html)->not->toContain('<a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">');
});

it('can auto embed bilibili links', function () {
    $action = new RenderMarkdown();
    $markdown = "https://www.bilibili.com/video/BV1xx411c7mD";
    
    $html = $action->execute($markdown);
    
    expect($html)->toContain('iframe src="//player.bilibili.com/player.html?bvid=BV1xx411c7mD');
});

it('strips raw HTML embedded by post authors to prevent stored XSS', function () {
    $action = new RenderMarkdown();
    $markdown = "Hello\n\n<script>alert('xss')</script>\n\n<img src=x onerror=\"alert('xss')\">";

    $html = $action->execute($markdown);

    expect($html)->not->toContain('<script>')
        ->not->toContain('onerror=');
});
