<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AssetTransaction;
use App\Models\Asset;

class AssetTransactionFactory extends Factory
{
    protected $model = AssetTransaction::class;

    public function definition(): array
    {
        return [
            'ops_id' => strtoupper($this->faker->lexify('???')) . '-' . $this->faker->unique()->numerify('####'),
            'asset_id' => Asset::factory(),
            'check_in' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'check_out' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('-5 days', 'now') : null,
            'created_by' => $this->faker->name(),
            'status' => function (array $attributes) {
                if ($attributes['check_out']) {
                    return 'complete';
                }

                return $this->faker->randomElement(['in use', 'overtime']);
            },
            'remarks' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the transaction is currently active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'check_out' => null,
            'status' => 'in use',
        ]);
    }

    /**
     * Indicate that the transaction is complete.
     */
    public function complete(): static
    {
        return $this->state(fn(array $attributes) => [
            'check_out' => $this->faker->dateTimeBetween('-5 days', 'now'),
            'status' => 'complete',
        ]);
    }

    /**
     * Indicate that the transaction is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn(array $attributes) => [
            'check_out' => null,
            'status' => 'overtime',
            'check_in' => $this->faker->dateTimeBetween('-15 days', '-8 days'), // More than 7 days ago
        ]);
    }
}
