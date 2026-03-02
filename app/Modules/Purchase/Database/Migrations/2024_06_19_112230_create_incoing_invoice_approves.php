<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncoingInvoiceApproves extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incoing_invoice_approves', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('idIncoingInvoice');
            $table->string('idLofted', 20);
            $table->string('idApproverDocuments', 20);
            $table->string('idUser', 20);
            $table->char('status', 1);
            $table->tinyInteger('nivel');
            $table->timestamps();
        });

        Schema::table('incoing_invoice_items', function (Blueprint $table) {
            $table->integer('lofted_approveds_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incoing_invoice_approves');

        Schema::table('incoing_invoice_items', function (Blueprint $table) {
            $table->dropColumn('lofted_approveds_id');
        });
    }
}
