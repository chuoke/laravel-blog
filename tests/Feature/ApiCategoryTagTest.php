<?php

it('creates a category via the API using the multi-language name format', function () {
    $response = $this->postJson('/api/blog/categories', [
        'name' => ['en' => 'Tech News'],
        'description' => ['en' => 'Latest tech updates'],
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('blog_categories', [
        'name' => json_encode(['en' => 'Tech News']),
    ]);
});

it('creates a tag via the API using the multi-language name format', function () {
    $response = $this->postJson('/api/blog/tags', [
        'name' => ['en' => 'Laravel'],
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('blog_tags', [
        'name' => json_encode(['en' => 'Laravel']),
    ]);
});
