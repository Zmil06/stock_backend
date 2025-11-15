<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Store;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $buying = $this->faker->numberBetween(500, 5000);
        $selling = $buying + $this->faker->numberBetween(200, 1500);

        return [
            'name' => ucfirst($this->faker->word()) . ' ' . $this->faker->randomElement(['Pack', 'Box', 'Set', 'Bottle']),
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'supplier_id' => Supplier::inRandomOrder()->first()?->id ?? Supplier::factory(),
            'store_id' => Store::inRandomOrder()->first()?->id ?? Store::factory(),
            'buying_price' => $buying,
            'selling_price' => $selling,
            'quantity' => $this->faker->numberBetween(0, 500),
            'threshold' => $this->faker->numberBetween(5, 30),
            'expiry_date' => $this->faker->optional(0.4)->dateTimeBetween('now', '+1 year'),
        ];
    }
}
