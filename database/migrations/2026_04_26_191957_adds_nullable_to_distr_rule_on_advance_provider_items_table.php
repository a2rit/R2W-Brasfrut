<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsNullableToDistrRuleOnAdvanceProviderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advance_provider_items', function (Blueprint $table) {
            $table->string('distrRule')->nullable()->change();
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
            $table->string('distrRule')->nullable(false)->change();
        });
    }
}
