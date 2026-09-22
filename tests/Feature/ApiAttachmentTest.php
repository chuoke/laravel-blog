<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('uploads an allowed file type', function () {
    $file = UploadedFile::fake()->image('cover.jpg');

    $response = $this->postJson('/api/blog/attachments', ['file' => $file]);

    $response->assertCreated();
    $this->assertDatabaseHas('blog_attachments', ['file_name' => 'cover.jpg']);
});

it('rejects a disallowed file type', function () {
    $file = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');

    $response = $this->postJson('/api/blog/attachments', ['file' => $file]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('file');
    $this->assertDatabaseMissing('blog_attachments', ['file_name' => 'malicious.php']);
});
