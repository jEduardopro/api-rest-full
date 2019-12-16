<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->paragraph(1),
        'quantity' => $faker->numberBetween($min = 5, $max = 100),
        'status' => $faker->randomElement([Product::PRODUCTO_DISPONIBLE,Product::PRODUCTO_NO_DISPONIBLE]),
        'image' => \Faker\Provider\Image::image(storage_path().'/app/public/img/products', 300,300, 'food', false),
        'seller_id' => \App\User::all()->random()->id,
    ];
});
