<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCostCenterInTablesPurchaseOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->text('costCenter')->nullable();
            $table->text('costCenter2')->nullable();
        });
        Schema::table('receipt_goods_expenses', function (Blueprint $table) {
            $table->text('costCenter')->nullable();
            $table->text('costCenter2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('costCenter');
            $table->dropColumn('costCenter2');
        });
        Schema::table('receipt_goods_expenses', function (Blueprint $table) {
            $table->dropColumn('costCenter');
            $table->dropColumn('costCenter2');
        });
       
    }
}
