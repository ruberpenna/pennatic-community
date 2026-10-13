<?php

namespace Database\Factories;

use App\Models\BoardFolder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Board>
 */
class BoardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_folder_id' => BoardFolder::factory(),
            'name' => fake()->unique()->words(2, true),
            'subjects' => null,
            'sort_order' => 0,
        ];
    }
}
