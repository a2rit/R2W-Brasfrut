<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdOrderInTablePurchaseQuotation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_quotation', function (Blueprint $table) {
       
            $table->text('id_order')->nullable();
            $table->text('code_order')->nullable();
            
        });
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_quotation', function (Blueprint $table) {
            $table->dropColumn('id_order');
            // $table->dropColumn('code_order');
           
        });
    
       
    }
}
