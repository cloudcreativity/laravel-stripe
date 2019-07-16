<?php

use CloudCreativity\LaravelStripe\Tests\TestAccount;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */

$factory->define(TestAccount::class, function (Faker $faker) {
    return [
        'id' => $faker->unique()->lexify('acct_????????????'),
        'name' => $faker->company,
    ];
});
