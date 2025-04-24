<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('calendar_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->foreignUlid('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_uid')->nullable();
            $table->timestamps();

            $table->index(['calendar_id', 'start_time']);
            $table->index(['location_id', 'start_time']);
            $table->unique(['calendar_id', 'external_uid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
