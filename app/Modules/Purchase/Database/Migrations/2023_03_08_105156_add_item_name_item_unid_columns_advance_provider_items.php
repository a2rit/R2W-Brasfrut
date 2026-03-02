<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItemNameItemUnidColumnsAdvanceProviderItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advance_provider_items', function (Blueprint $table) {
            $table->string("itemName", 256)->nullable();
            $table->string("itemUnd", 10)->nullable();
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
            $table->dropColumn('itemName');
            $table->dropColumn('itemUnd');
        });
    }
}
