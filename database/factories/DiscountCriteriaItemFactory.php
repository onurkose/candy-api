<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaItem;

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

$factory->define(DiscountCriteriaItem::class, function (Faker $faker) {
    return [
        'type' => 'coupon',
        'value' => strtoupper($faker->word) . $faker->randomNumber(3),
    ];
});
