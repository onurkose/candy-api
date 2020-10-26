<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Discounts\Models\Discount;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Discount::class, function (Faker $faker) {
    return [
        'attribute_data' => [
            'name' => [
                'en' => ucfirst($faker->unique()->company),
            ],
        ],
        'uses' => 0,
        'status' => 1,
        'start_at' => now(),
        'end_at' => now()->addYear(1),
    ];
});
