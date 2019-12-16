<?php

use App\Category;
use App\Product;
use App\Transaction;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        Storage::disk('public')->deleteDirectory('img/products');
        Storage::disk('public')->makeDirectory('img/products');

        User::flushEventListeners();
        Category::flushEventListeners();
        Product::flushEventListeners();
        Transaction::flushEventListeners();

        factory(User::class, 1000)->create();
        factory(Category::class, 35)->create();
        factory(Product::class, 1000)->create()->each(
            function($producto) {
                $categorias = Category::all()->random(mt_rand(1,5))->pluck('id');
                $producto->categories()->attach($categorias);
            }
        );

        // factory(Product::class, 5)->create();
        factory(Transaction::class, 1000)->create();
    }
}
