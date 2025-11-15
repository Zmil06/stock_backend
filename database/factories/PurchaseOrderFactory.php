<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(5, 100);
        $unitPrice = $this->faker->numberBetween(1000, 10000);
        $status = $this->faker->randomElement(['Delayed', 'Confirmed', 'Returned', 'Out for delivery']);

        return [
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            'supplier_id' => Supplier::inRandomOrder()->first()?->id ?? Supplier::factory(),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'quantity' => $quantity,
            'order_value' => $quantity * $unitPrice,
            'order_date' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'expected_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => $status,
            'received' => $status === 'Delivered',
            'received_date' => $status === 'Delivered' ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
        ];
    }
}
