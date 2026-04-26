<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsNullableToProjectOnAdvanceProviderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advance_provider_items', function (Blueprint $table) {
            $table->string('project')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advance_provider_items', function (Blueprint $table) {
            $table->string('project')->nullable(false)->change();
        });
    }
}
