<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Store;
use App\Models\Product;
use App\Models\PurchaseOrder;   
use App\Models\Sale;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        User::factory(10)->create();


        Category::factory(10)->create();
        Supplier::factory(8)->create();
        Store::factory(3)->create();

        // Crée des produits
        Product::factory(50)->create();

        // Crée des commandes fournisseurs
        PurchaseOrder::factory(30)->create();


        // Crée des ventes
        Sale::factory(40)->create();
}
}
