<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('blog_cover_generations', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'is_active']);
            $table->dropColumn('request_hash');
            $table->unique(['post_id', 'is_active']);
        });
    }

    public function down(): void
    {
        $activeUsers = DB::table('blog_cover_generations')->where('is_active', true)->pluck('user_id');

        if ($activeUsers->count() !== $activeUsers->unique()->count()) {
            throw new LogicException('Cannot restore the user-level cover limit while a user has multiple active tasks.');
        }

        Schema::table('blog_cover_generations', function (Blueprint $table) {
            $table->dropUnique(['post_id', 'is_active']);
            $table->string('request_hash', 64)->nullable();
        });

        DB::table('blog_cover_generations')->orderBy('id')->chunkById(500, function ($rows): void {
            foreach ($rows as $row) {
                $data = $row->data === null ? null : json_decode($row->data, true);
                $input = $data ?? $row->id;

                DB::table('blog_cover_generations')
                    ->where('id', $row->id)
                    ->update(['request_hash' => hash('sha256', json_encode($input))]);
            }
        });

        Schema::table('blog_cover_generations', function (Blueprint $table) {
            $table->string('request_hash', 64)->nullable(false)->change();
            $table->unique(['user_id', 'is_active']);
        });
    }
};
