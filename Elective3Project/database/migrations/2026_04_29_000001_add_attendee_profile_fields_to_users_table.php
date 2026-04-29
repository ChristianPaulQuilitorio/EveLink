<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'contact_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('contact_number', 11)->nullable()->after('email');
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('attendee')->after('contact_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'contact_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('contact_number');
            });
        }
    }
};
