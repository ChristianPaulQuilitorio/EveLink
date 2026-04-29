<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->timestamp('present_at')->nullable()->after('attendance_status');
        });

        DB::table('registrations')
            ->where('attendance_status', 'Present')
            ->whereNull('present_at')
            ->update(['present_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('present_at');
        });
    }
};
