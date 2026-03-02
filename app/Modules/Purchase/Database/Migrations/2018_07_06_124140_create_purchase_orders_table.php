<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idUser',30);
            $table->string('codSAP',30)->nullable();
            $table->string('code',30);
            $table->string('cardCode',15);
            $table->string('identification',25)->nullable();
            $table->text('cardName');
            $table->date('docDate');
            $table->date('docDueDate');
            $table->date('taxDate');
            $table->double('quotation')->nullable();
            $table->double('docTotal')->nullable();
            $table->bigInteger('paymentTerms');
            $table->string('freightDocument',10)->nullable();
            $table->double('discountPercent')->nullable();
            $table->string('branch',10)->nullable();
            $table->string('coin',5)->nullable();
            $table->text('comments',254)->nullable();
            $table->string('status',10);
            $table->double('paindSum',50)->nullable();
            $table->string('message')->nullable();
            $table->boolean('is_locked')->defaut(0);
            $table->boolean('dbUpdate')->default(0);
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
        Schema::dropIfExists('purchase_orders');
    }
}
