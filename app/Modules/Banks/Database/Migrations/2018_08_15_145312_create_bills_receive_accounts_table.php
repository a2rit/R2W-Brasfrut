<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillsReceiveAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills_receive_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idUser',10);
            $table->string('codSAP',10)->nullable();
            $table->string('code');
            $table->date('docDate');
            $table->date('docDueDate');
            $table->date('taxDate');
            $table->text('comments')->nullable();
            $table->double('docTotal')->nullable();
            $table->boolean('is_locked')->default('0');
            $table->text('message')->nullable();
            $table->char('status')->default('1');
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
        Schema::dropIfExists('bills_receive_accounts');
    }
}
