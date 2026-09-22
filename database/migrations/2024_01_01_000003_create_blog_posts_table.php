<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 32)->unique()->index();
            $table->unsignedBigInteger('article_id')->nullable()->index();
            $table->string('author_id', 36)->index();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content');

            $table->string('status')->default('draft'); // draft, published, archived
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_pinned')->default(false);

            $table->unsignedBigInteger('cover_image_id')->nullable();
            $table->string('source_type')->default('original'); // original, repost, translated
            $table->string('source_url')->nullable();
            $table->string('language')->default(config('blog.locale', 'en'));

            $table->unsignedInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['article_id', 'language']);

            $table->foreign('category_id')->references('id')->on('blog_categories')->onDelete('set null');
            $table->foreign('cover_image_id')->references('id')->on('blog_attachments')->onDelete('set null');
        });

        // Self-referencing FK added after table creation (some DB drivers, e.g.
        // SQLite, don't support declaring a FK on a table referencing itself
        // within the same CREATE TABLE statement). Cascading on delete means
        // force-deleting the original post of an article group also removes
        // all of its translations, since they're really one logical article.
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreign('article_id')->references('id')->on('blog_posts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
