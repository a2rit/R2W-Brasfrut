<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdItemPurchaseOrderColumnIncoingInvoiceItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoing_invoice_items', function (Blueprint $table) {
            $table->integer('idItemPurchaseOrder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incoing_invoice_items', function (Blueprint $table) {
            $table->dropColumn('idItemPurchaseOrder');
        });
    }
}
