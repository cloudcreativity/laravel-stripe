<?php

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
            $table->json('company')->nullable();
            $table->string('country', 3)->nullable();
            $table->timestamp('created')->nullable();
            $table->string('default_currency', 3)->nullable();
            $table->boolean('details_submitted')->nullable();
            $table->string('email')->nullable();
            $table->json('individual')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->boolean('payouts_enabled')->nullable();
            $table->string('refresh_token')->nullable();
            $table->json('requirements')->nullable();
            $table->json('settings')->nullable();
            $table->json('tos_acceptance')->nullable();
            $table->string('type')->nullable();
        });

        Schema::create('stripe_events', function (Blueprint $table) {
            $table->string('id', 255)->primary()->collate(LaravelStripe::ID_DATABASE_COLLATION);
            $table->timestamps();
            $table->string('account_id', 255)->nullable()->collate(LaravelStripe::ID_DATABASE_COLLATION);
            $table->string('api_version');
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
