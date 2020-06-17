<?php
/**
 * Copyright 2020 Cloud Creativity Limited
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

use CloudCreativity\LaravelStripe\Models\StripeAccount;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */

$factory->define(StripeEvent::class, function (Faker $faker) {
    return [
        'id' => $faker->unique()->lexify('evt_????????????????'),
        'api_version' => $faker->date(),
        'created' => $faker->dateTimeBetween('-1 hour', 'now'),
        'livemode' => $faker->boolean,
        'pending_webhooks' => $faker->numberBetween(0, 100),
        'type' => $faker->randomElement([
            'charge.failed',
            'payment_intent.succeeded',
        ]),
    ];
});

$factory->state(StripeEvent::class, 'connect', function () {
    return [
        'account_id' => factory(StripeAccount::class)->create()->getKey(),
    ];
});
