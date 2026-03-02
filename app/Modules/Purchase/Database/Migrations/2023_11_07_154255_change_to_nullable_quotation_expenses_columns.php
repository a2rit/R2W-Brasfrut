<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeToNullableQuotationExpensesColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_quotation_expenses', function (Blueprint $table) {
            $table->string('tax', 20)->nullable()->change();
            $table->string('project', 30)->nullable()->change();
            $table->string('distributionRule', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_quotation_expenses', function (Blueprint $table) {
            //
        });
    }
}
