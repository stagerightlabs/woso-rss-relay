<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // https://github.com/nunomaduro/laravel-optimize-database
        DB::unprepared(
            <<<SQL
            PRAGMA auto_vacuum = incremental;
            PRAGMA journal_mode = WAL;
            PRAGMA page_size = 32768;
            SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
