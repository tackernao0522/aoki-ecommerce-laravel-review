<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            OwnersTableSeeder::class,
            AdminsTableSeeder::class,
            ShopsTableSeeder::class,
            ImagesTableSeeder::class,
            CategoriesTableSeeder::class,
            // ProductsTableSeeder::class,
            // StocksTableSeeder::class,
            UsersTableSeeder::class,
        ]);
        Stock::factory(100)->create();
    }
}
