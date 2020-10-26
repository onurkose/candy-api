<?php

use Faker\Generator as Faker;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaSet;

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

$factory->define(DiscountCriteriaSet::class, function (Faker $faker) {
    return [
        'scope' => 'all',
        'outcome' => 1,
    ];
});
