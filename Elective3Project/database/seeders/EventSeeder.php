<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            // Open events (upcoming with available slots)
            [
                'event_name' => 'Barangay Disaster Preparedness Seminar',
                'description' => 'A community training on earthquake and flood response protocols.',
                'event_date' => now()->addDays(5)->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '11:00:00',
                'venue' => 'Barangay Hall, Quezon City, Metro Manila',
                'max_slots' => 180,
                'target_registrations' => 52,
            ],
            [
                'event_name' => 'Youth Leadership Camp Orientation',
                'description' => 'Orientation and team-building kickoff for SK youth leaders.',
                'event_date' => now()->addDays(9)->toDateString(),
                'start_time' => '13:00:00',
                'end_time' => '16:00:00',
                'venue' => 'SK Function Room, Pasig City, Metro Manila',
                'max_slots' => 140,
                'target_registrations' => 48,
            ],
            [
                'event_name' => 'Barangay Health and Wellness Fair',
                'description' => 'Free blood pressure checks, consultation desks, and nutrition talks.',
                'event_date' => now()->addDays(13)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '15:00:00',
                'venue' => 'Covered Court, Cebu City, Cebu',
                'max_slots' => 220,
                'target_registrations' => 75,
            ],
            [
                'event_name' => 'Community Livelihood Skills Workshop',
                'description' => 'Hands-on workshop on food processing and microenterprise basics.',
                'event_date' => now()->addDays(17)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '14:00:00',
                'venue' => 'Multi-Purpose Center, Davao City, Davao del Sur',
                'max_slots' => 160,
                'target_registrations' => 44,
            ],

            // Full events (upcoming but at max capacity)
            [
                'event_name' => 'Family Day Sports League Finals',
                'description' => 'Final rounds of inter-purok basketball and volleyball games.',
                'event_date' => now()->addDays(4)->toDateString(),
                'start_time' => '15:00:00',
                'end_time' => '20:00:00',
                'venue' => 'Barangay Sports Complex, Bacolod City, Negros Occidental',
                'max_slots' => 60,
                'target_registrations' => 60,
            ],
            [
                'event_name' => 'Senior Citizens Social and Medical Checkup',
                'description' => 'Quarterly gathering with basic health screening for seniors.',
                'event_date' => now()->addDays(7)->toDateString(),
                'start_time' => '08:30:00',
                'end_time' => '12:30:00',
                'venue' => 'Barangay Evacuation Center, Iloilo City, Iloilo',
                'max_slots' => 80,
                'target_registrations' => 80,
            ],
            [
                'event_name' => 'Women Entrepreneurship Mentoring Session',
                'description' => 'Mentor-led sessions for startup and home-based business owners.',
                'event_date' => now()->addDays(11)->toDateString(),
                'start_time' => '09:30:00',
                'end_time' => '13:00:00',
                'venue' => 'Community Learning Hub, Cagayan de Oro, Misamis Oriental',
                'max_slots' => 50,
                'target_registrations' => 50,
            ],

            // Concluded events (past dates)
            [
                'event_name' => 'Barangay Clean-Up Drive',
                'description' => 'Neighborhood clean-up campaign across priority streets and esteros.',
                'event_date' => now()->subDays(12)->toDateString(),
                'start_time' => '06:30:00',
                'end_time' => '10:30:00',
                'venue' => 'Rizal Park Area, Manila, Metro Manila',
                'max_slots' => 120,
                'target_registrations' => 86,
            ],
            [
                'event_name' => 'Barangay Nutrition Month Culmination',
                'description' => 'Nutrition talks, healthy cooking demo, and feeding program.',
                'event_date' => now()->subDays(8)->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'venue' => 'Public Plaza, Legazpi City, Albay',
                'max_slots' => 90,
                'target_registrations' => 90,
            ],
            [
                'event_name' => 'Fire Prevention and Safety Drill',
                'description' => 'Community evacuation and response drills with BFP volunteers.',
                'event_date' => now()->subDays(5)->toDateString(),
                'start_time' => '14:00:00',
                'end_time' => '17:00:00',
                'venue' => 'Town Gymnasium, Baguio City, Benguet',
                'max_slots' => 100,
                'target_registrations' => 72,
            ],
            // Full event (upcoming but at max capacity)
            [
                'event_name' => 'Family Day Sports League Finals',
                'description' => 'Final rounds of inter-purok basketball and volleyball games.',
                'event_date' => now()->addDays(4)->toDateString(),
                'start_time' => '15:00:00',
                'end_time' => '20:00:00',
                'venue' => 'Barangay Sports Complex, Bacolod City, Negros Occidental',
                'max_slots' => 60,
                'target_registrations' => 60,  // Same as max_slots = FULL
            ],
            [
                'event_name' => 'Your Event Name',
                'description' => 'Description here.',
                'event_date' => now()->addDays(6)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '14:00:00',
                'venue' => 'Your Venue',
                'max_slots' => 100,
                'target_registrations' => 100,  // Full = matching max_slots
            ],
                    ];

        foreach ($events as $payload) {
            $targetRegistrations = (int) $payload['target_registrations'];
            unset($payload['target_registrations']);

            $event = Event::query()->updateOrCreate(
                ['event_name' => $payload['event_name']],
                $payload
            );

            $this->seedEventRegistrations($event, $targetRegistrations);
        }
    }

    private function seedEventRegistrations(Event $event, int $count): void
    {
        Registration::query()
            ->where('event_id', $event->id)
            ->where('email', 'like', 'seed-%@evelink.ph')
            ->delete();

        if ($count <= 0) {
            return;
        }

        $rows = [];
        $statuses = ['Pending', 'Present', 'Absent'];
        $seedTag = Str::slug($event->event_name);

        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'event_id' => $event->id,
                'first_name' => 'Attendee',
                'last_name' => str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'email' => 'seed-' . $seedTag . '-' . $i . '@evelink.ph',
                'contact_number' => '09' . str_pad((string) (810000000 + $i), 9, '0', STR_PAD_LEFT),
                'attendance_status' => $statuses[$i % count($statuses)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Registration::query()->insert($rows);
    }
}
