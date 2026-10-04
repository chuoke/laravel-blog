<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blog_cover_generations', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 36)->index();
            $table->string('request_hash', 64);
            $table->string('status')->default('pending')->index();
            $table->boolean('is_active')->nullable()->default(true);
            $table->json('data')->nullable();
            $table->unsignedBigInteger('attachment_id')->nullable();
            $table->timestamps();

            $table->foreign('attachment_id')->references('id')->on('blog_attachments')->nullOnDelete();
            $table->unique(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_cover_generations');
    }
};
