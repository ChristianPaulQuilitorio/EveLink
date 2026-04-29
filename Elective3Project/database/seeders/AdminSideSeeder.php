<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminSideSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdminUser();
        $this->seedEventsAndRegistrations();
    }

    private function seedAdminUser(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'username') && Schema::hasColumn('users', 'full_name')) {
            DB::table('users')->updateOrInsert(
                ['email' => 'admin@evelink.local'],
                [
                    'username' => 'admin',
                    'full_name' => 'EveLink Administrator',
                    'contact_number' => null,
                    'role' => 'admin',
                    'password' => Hash::make('password123'),
                    'remember_token' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            return;
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@evelink.local'],
            [
                'name' => 'EveLink Administrator',
                'contact_number' => null,
                'role' => 'admin',
                'password' => Hash::make('password123'),
                'remember_token' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function seedEventsAndRegistrations(): void
    {
        if (! Schema::hasTable('events') || ! Schema::hasTable('registrations')) {
            return;
        }

        $eventOneId = DB::table('events')->updateOrInsert(
            ['event_name' => 'Barangay Health Check'],
            [
                'description' => 'Community health screening and awareness program.',
                'event_date' => now()->addDays(3)->toDateString(),
                'venue' => 'Community Hall',
                'max_slots' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $eventTwoId = DB::table('events')->updateOrInsert(
            ['event_name' => 'SK Sports Fest'],
            [
                'description' => 'Inter-purok sports activity for youth participants.',
                'event_date' => now()->addDays(7)->toDateString(),
                'venue' => 'Barangay Sports Plaza',
                'max_slots' => 120,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $eventOne = DB::table('events')->where('event_name', 'Barangay Health Check')->value('id');
        $eventTwo = DB::table('events')->where('event_name', 'SK Sports Fest')->value('id');

        if (! $eventOne || ! $eventTwo) {
            return;
        }

        DB::table('registrations')->updateOrInsert(
            ['event_id' => $eventOne, 'email' => 'maria.clara@example.com'],
            [
                'first_name' => 'Maria',
                'last_name' => 'Clara',
                'contact_number' => '09171234567',
                'attendance_status' => 'Present',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('registrations')->updateOrInsert(
            ['event_id' => $eventOne, 'email' => 'juan.delacruz@example.com'],
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'contact_number' => '09182345678',
                'attendance_status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('registrations')->updateOrInsert(
            ['event_id' => $eventTwo, 'email' => 'jose.rizal@example.com'],
            [
                'first_name' => 'Jose',
                'last_name' => 'Rizal',
                'contact_number' => '09193456789',
                'attendance_status' => 'Absent',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}