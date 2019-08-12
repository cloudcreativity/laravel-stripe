<?php
/**
 * Copyright 2019 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Models\StripeAccount;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */

$factory->define(StripeAccount::class, function (Faker $faker) {
    return [
        'id' => $faker->unique()->lexify('acct_????????????????'),
        'country' => $faker->randomElement(['AU', 'GB', 'US']),
        'default_currency' => $faker->randomElement(Config::currencies()->all()),
        'details_submitted' => $faker->boolean(75),
        'email' => $faker->email,
        'payouts_enabled' => $faker->boolean(75),
        'type' => $faker->randomElement(['standard', 'express', 'custom']),
    ];
});
