<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->string('event_name', 100);
                $table->text('description')->nullable();
                $table->date('event_date');
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('venue', 150);
                $table->unsignedInteger('max_slots');
                $table->timestamps();

                $table->index('event_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};