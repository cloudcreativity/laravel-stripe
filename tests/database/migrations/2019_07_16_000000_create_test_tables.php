<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestTables extends Migration
{

    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_accounts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
            $table->string('name');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_accounts');
    }
}
