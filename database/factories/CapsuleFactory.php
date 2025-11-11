<?php

namespace Database\Factories;

use App\Models\Capsule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Capsule>
 */
class CapsuleFactory extends Factory
{
    protected $model = Capsule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'capsule_serial' => $this->faker->unique()->bothify('C###'),
            'capsule_id' => $this->faker->bothify('dragon#'),
            'status' => $this->faker->randomElement(['active', 'retired', 'destroyed', 'unknown']),
            'original_launch' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'missions_count' => $this->faker->numberBetween(0, 10),
            'details' => $this->faker->optional()->sentence(),
            'raw_data' => json_encode([
                'capsule_serial' => $this->faker->bothify('C###'),
                'status' => 'active',
                'missions' => []
            ]),
        ];
    }
}
