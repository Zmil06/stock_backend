<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    
       public function definition(): array
    {
        return [
            'name' => 'Magasin ' . $this->faker->city(),
            'location' => $this->faker->address(),
            'manager_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
    
}
