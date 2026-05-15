<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('registrations')) {
            Schema::table('registrations', function (Blueprint $table): void {
                $table->index(['event_id', 'created_at'], 'registrations_event_created_at_index');
                $table->index(['event_id', 'last_name', 'first_name'], 'registrations_event_name_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('registrations')) {
            Schema::table('registrations', function (Blueprint $table): void {
                $table->dropIndex('registrations_event_created_at_index');
                $table->dropIndex('registrations_event_name_index');
            });
        }
    }
};
