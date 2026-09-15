<?php

namespace Database\Factories;

use App\Models\Feed;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $guid = $this->faker->unique()->uuid();

        return [
            'feed_id' => Feed::factory(),
            'guid' => $guid,
            'title' => $this->faker->sentence(),
            'url' => $this->faker->unique()->url(),
            'content' => '<p>'.$this->faker->paragraph().'</p>',
            'published_at' => now()->subHour(),
        ];
    }

    /**
     * State for an article that has already been read.
     */
    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }
}
