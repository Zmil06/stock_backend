<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $quantity = $this->faker->numberBetween(1, 20);

        return [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'sale_date' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'selling_price' => $product->selling_price,
            'buying_price' => $product->buying_price,
            'total_value' => $quantity * $product->selling_price,
        ];
    }
}
