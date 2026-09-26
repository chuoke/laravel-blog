<?php

it('requires authentication for API writes by default', function () {
    $this->postJson('/api/blog/posts', [
        'title' => 'Unauthenticated post',
        'content' => 'This request must not create content.',
    ])->assertUnauthorized();
});
