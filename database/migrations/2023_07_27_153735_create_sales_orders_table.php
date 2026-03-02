<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string("code", 20);
            $table->integer('codSAP')->nullable();
            $table->string('cardCode', 20);
            $table->date('docDate');
            $table->date('docDueDate');
            $table->date('taxDate');
            $table->integer('slpCode')->nullable();
            $table->string('comments', 254)->nullable();
            $table->string('jrnlMemo', 254)->nullable();
            $table->string('project', 254)->nullable();
            $table->integer('status');
            $table->decimal('docTotal', 20, 2);
            $table->decimal('discount', 20, 2);
            $table->integer('paymentCondition')->nullable();
            $table->integer('nfc_id');
            $table->string('chave', 50);
            $table->string('message', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_orders');
    }
}
