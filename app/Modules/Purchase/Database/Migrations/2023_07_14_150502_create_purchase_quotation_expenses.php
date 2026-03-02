<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseQuotationExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_quotation_expenses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idPurchaseQuotation');
            $table->bigInteger('expenseCode');
            $table->text('tax');
            $table->double('lineTotal', 15);
            $table->string('project', 20);
            $table->string('distributionRule', 8);
            $table->text('costCenter')->nullable();
            $table->text('costCenter2')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
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
            $table->dropColumn('id');
            $table->dropColumn('idPurchaseQuotation');
            $table->dropColumn('expenseCode');
            $table->dropColumn('tax');
            $table->dropColumn('lineTotal');
            $table->dropColumn('project');
            $table->dropColumn('distributionRule');
            $table->dropColumn('comments');
        });
    }
}
