<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXmlReceiptGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xml_receipt_goods', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idUser',30);
            $table->string('idPurchaseOrders',30)->nullable();
            $table->string('codSAP',30)->nullable();
            $table->string('code',30)->nullable();
            $table->string('cardCode',15);
            $table->date('docDate');
            $table->date('docDueDate');
            $table->date('taxDate');
            $table->bigInteger('paymentTerms');
            $table->double('freight')->nullable();
            $table->double('discPrcnt')->nullable();
            $table->string('branch',10)->nullable();
            $table->string('coin',2)->nullable();
            $table->text('comments')->nullable();
            $table->double('docTotal')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_locked')->default('0');
            $table->text('status');
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
        Schema::dropIfExists('xml_receipt_googds');
    }
}
