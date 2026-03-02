<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//Necessário mudança em quantidade de caracteres das colunas 
class ModifyIncoing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incoing_invoice_expenses', function (Blueprint $table) {
            $table->string('project')->nullable()->change();
            $table->string('distributionRule')->nullable()->change();
            // $table->string('codCost')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incoing_invoice_expenses', function (Blueprint $table) {
            $table->string('project',20)->change();
            $table->string('distributionRule',8)->change();
            // $table->string('codCost',10)->change();
        });
    }
}
