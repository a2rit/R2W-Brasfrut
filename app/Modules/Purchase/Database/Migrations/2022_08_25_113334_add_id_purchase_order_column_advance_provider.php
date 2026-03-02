<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdPurchaseOrderColumnAdvanceProvider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advance_provider', function (Blueprint $table) {
            $table->integer('idPurchaseOrder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advance_provider', function (Blueprint $table) {
            $table->dropColumn('idPurchaseOrder');
        });
    }
}
