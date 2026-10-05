<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('blog_cover_generations', function (Blueprint $table) {
            $table->string('failure_reason', 1000)->nullable()->after('attachment_id');
        });
    }

    public function down(): void
    {
        Schema::table('blog_cover_generations', function (Blueprint $table) {
            $table->dropColumn('failure_reason');
        });
    }
};
