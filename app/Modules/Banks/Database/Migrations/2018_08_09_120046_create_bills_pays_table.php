<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillsPaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills_pays', function (Blueprint $table) {
          $table->increments('id');
          $table->string('idUser');
          $table->string('codSAP')->nullable();
          $table->string('code')->nullable();
          $table->string('cardCode',15);
          $table->text('cardName');
          $table->double('docTotal');
          $table->text('identification')->nullable();
          $table->string('coin',15)->nullable();
          $table->double('quotation')->nullable();
          $table->date('docDate');
          $table->date('docDueDate');
          $table->date('taxDate');
          $table->text('comments')->nullable();
          $table->boolean('is_locked')->default(0);
          $table->text('message')->nullable();
          $table->char('status')->default(1);
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
        Schema::dropIfExists('bills_pays');
    }
}
