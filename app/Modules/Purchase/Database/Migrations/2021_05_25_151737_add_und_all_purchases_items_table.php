<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//Necessário mudança em quantidade de caracteres das colunas 
class AddUndAllPurchasesItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('itemUnd')->nullable();
            
        });
        Schema::table('purchase_quotation_items', function (Blueprint $table) {
            $table->string('itemUnd')->nullable();
            $table->string('lastPrice')->nullable();
            
        });
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->string('itemUnd')->nullable();
            $table->string('itemName')->nullable();
            
        });
        Schema::table('incoing_invoice_items', function (Blueprint $table) {
            $table->string('itemUnd')->nullable();
            
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
            $table->dropColumn('itemUnd');
        });
        Schema::table('purchase_quotation_items', function (Blueprint $table) {
            $table->dropColumn('itemUnd');
            $table->dropColumn('lastPrice');
        });
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->dropColumn('itemUnd');
            $table->dropColumn('itemName');
        });
        Schema::table('incoing_invoice_items', function (Blueprint $table) {
            $table->dropColumn('itemUnd');
        });
    }
}
