<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdRequestInTablePurchaseQuotation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_quotation', function (Blueprint $table) {
            $table->boolean('isRequest')->default(0);
            $table->text('idRequest')->nullable();
            
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
            $table->dropColumn('isRequest');
            $table->dropColumn('idRequest');
           
        });
    
       
    }
}
