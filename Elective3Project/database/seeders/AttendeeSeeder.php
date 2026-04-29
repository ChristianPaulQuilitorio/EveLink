<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class AttendeeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed attendees for each event.
     */
    public function run(): void
    {
        $events = Event::query()->get();

        foreach ($events as $event) {
            // Create 20-40 attendees per event
            $attendeeCount = random_int(20, 40);

            $rows = Registration::factory()
                ->count($attendeeCount)
                ->make(['event_id' => $event->id])
                ->map(function (Registration $registration): array {
                    return [
                        'event_id' => $registration->event_id,
                        'first_name' => $registration->first_name,
                        'last_name' => $registration->last_name,
                        'email' => $registration->email,
                        'contact_number' => $registration->contact_number,
                        'attendance_status' => $registration->attendance_status,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->all();

            DB::table('registrations')->insert($rows);

            $this->command->info("Created {$attendeeCount} attendees for event: {$event->event_name}");
        }
    }
}
