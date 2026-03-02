<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItemNameItemUnidColumnsStockLoanItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_loans_items', function (Blueprint $table) {
            $table->string('itemName', 254)->nullable();
            $table->string('itemUnd', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_loans_items', function (Blueprint $table) {
            $table->dropColumn('itemName');
            $table->dropColumn('itemUnd');
        });
    }
}
