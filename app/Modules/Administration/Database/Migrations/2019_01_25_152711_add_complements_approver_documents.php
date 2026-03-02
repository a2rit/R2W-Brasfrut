<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComplementsApproverDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('approver_documents', function (Blueprint $table) {
            $table->string('nameApproverUser')->nullable();
            $table->string('nameLoftedApproveds')->nullable();

        });
    }
   
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('approver_documents', function (Blueprint $table) {
            $table->string('nameApproverUser')->nullable();
            $table->string('nameLoftedApproveds')->nullable();
        });
    }
}
