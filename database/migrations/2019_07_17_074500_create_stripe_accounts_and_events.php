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

use CloudCreativity\LaravelStripe\LaravelStripe;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStripeAccountsAndEvents extends Migration
{

    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_accounts', function (Blueprint $table) {
            $table->string('id', 255)->primary()->collate(LaravelStripe::ID_DATABASE_COLLATION);
            $table->timestamps();
            $table->softDeletes();
            $table->json('business_profile')->nullable();
            $table->string('business_type')->nullable();
            $table->json('capabilities')->nullable();
            $table->boolean('charges_enabled')->nullable();
            $table->json('company')->nullable();
            $table->string('country', 3)->nullable();
            $table->timestamp('created')->nullable();
            $table->string('default_currency', 3)->nullable();
            $table->boolean('details_submitted')->nullable();
            $table->string('email')->nullable();
            $table->json('individual')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedInteger('owner_id')->nullable();
            $table->boolean('payouts_enabled')->nullable();
            $table->string('refresh_token')->nullable();
            $table->json('requirements')->nullable();
            $table->json('settings')->nullable();
            $table->string('token_scope')->nullable();
            $table->json('tos_acceptance')->nullable();
            $table->string('type')->nullable();
        });

        Schema::create('stripe_events', function (Blueprint $table) {
            $table->string('id', 255)->primary()->collate(LaravelStripe::ID_DATABASE_COLLATION);
            $table->timestamps();
            $table->string('account_id', 255)->nullable()->collate(LaravelStripe::ID_DATABASE_COLLATION);
            $table->date('api_version');
            $table->timestamp('created');
            $table->boolean('livemode');
            $table->unsignedInteger('pending_webhooks');
            $table->string('type');
            $table->json('request')->nullable();
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stripe_events');
        Schema::dropIfExists('stripe_accounts');
    }
}
