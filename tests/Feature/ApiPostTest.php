<?php

use Illuminate\Foundation\Auth\User;

beforeEach(function () {
    $user = new User();
    $user->forceFill(['id' => 1])->exists = true;

    $this->actingAs($user);
});

it('creates a post via the API for the authenticated author', function () {
    $response = $this->postJson('/api/blog/posts', [
        'title' => 'Hello World',
        'content' => 'Some content',
        'author_id' => 999,
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('blog_posts', [
        'title' => 'Hello World',
        'author_id' => 1,
    ]);
});

it('validates API post creation requests', function () {
    $response = $this->postJson('/api/blog/posts', [
        'content' => 'Some content',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('title');
});
