<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('registrations')) {
            Schema::create('registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
                $table->string('first_name', 50);
                $table->string('last_name', 50);
                $table->string('email', 100);
                $table->string('contact_number', 11);
                $table->enum('attendance_status', ['Pending', 'Present', 'Absent'])->default('Pending');
                $table->timestamps();

                $table->unique(['event_id', 'email']);
                $table->index(['event_id', 'attendance_status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};