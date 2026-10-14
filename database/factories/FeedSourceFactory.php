<?php

namespace Database\Factories;

use App\Models\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeedSource>
 */
class FeedSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'name' => 'Test feed',
            'url' => 'https://feeds.example.test/'.fake()->unique()->slug().'.xml',
            'is_active' => true,
            'max_items' => 15,
        ];
    }
}
