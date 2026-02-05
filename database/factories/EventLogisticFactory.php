<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventLogistic>
 */
class EventLogisticFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->sentence(3);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'athletes_inscriptions_raw' => null,
            'inscriptions_data' => [],
            'raw_schedule' => [],
            'participants_data' => [],
            'transport_plan' => [],
            'stay_plan' => [],
            'settings' => [
                'vitesse_bus' => 100,
                'vitesse_voiture' => 120,
                'temps_prep_min' => 90,
                'temps_recup_min' => 60,
                'distance_km' => 100,
                'start_date' => now()->toDateString(),
            ],
        ];
    }
}
