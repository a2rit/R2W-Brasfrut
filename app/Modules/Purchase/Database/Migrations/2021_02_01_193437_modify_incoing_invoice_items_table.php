<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//Necessário mudança em quantidade de caracteres das colunas 
class ModifyIncoingInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoing_invoice_items', function (Blueprint $table) {
            $table->string('codUse')->change();
            $table->string('codProject')->change();
            $table->string('codCost')->change();
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
            $table->string('codUse',10)->change();
            $table->string('codProject',10)->change();
            $table->string('codCost',10)->change();
        });
    }
}
