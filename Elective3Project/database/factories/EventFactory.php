<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+3 months');
        $durationHours = $this->faker->numberBetween(1, 6);
        $endDate = (clone $startDate)->modify("+{$durationHours} hours");

        return [
            'event_name' => $this->faker->sentence(3),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'event_date' => $startDate->format('Y-m-d'),
            'start_time' => $startDate->format('H:i'),
            'end_time' => $endDate->format('H:i'),
            'venue' => $this->faker->optional(0.9)->company() ?: $this->faker->streetName(),
            'max_slots' => $this->faker->numberBetween(20, 500),
        ];
    }
}
