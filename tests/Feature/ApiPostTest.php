<?php

it('creates a post via the API using an explicit author_id when unauthenticated', function () {
    $response = $this->postJson('/api/blog/posts', [
        'title' => 'Hello World',
        'content' => 'Some content',
        'author_id' => 1,
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('blog_posts', [
        'title' => 'Hello World',
        'author_id' => 1,
    ]);
});

it('rejects an API post creation without an author_id when unauthenticated', function () {
    $response = $this->postJson('/api/blog/posts', [
        'title' => 'Hello World',
        'content' => 'Some content',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('author_id');
});

it('ignores a spoofed author_id and uses the authenticated user instead', function () {
    $user = new \Illuminate\Foundation\Auth\User();
    $user->forceFill(['id' => 42])->exists = true;

    $response = $this->actingAs($user)->postJson('/api/blog/posts', [
        'title' => 'Hello World',
        'content' => 'Some content',
        'author_id' => 999,
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('blog_posts', [
        'title' => 'Hello World',
        'author_id' => 42,
    ]);
});
