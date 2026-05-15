<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'contact_number' => substr(preg_replace('/[^0-9]/', '', $this->faker->phoneNumber()), 0, 11),
            'attendance_status' => $this->faker->randomElement(['Present', 'Absent', 'Pending']),
        ];
    }
}
