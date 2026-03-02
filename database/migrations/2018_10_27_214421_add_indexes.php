<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nfc', function (Blueprint $table){
            $table->index('pv_id');
        });

        Schema::table('nfc_itens', function (Blueprint $table){
            $table->index('nfc_id');
        });

        Schema::table('porcionamentos', function (Blueprint $table){
            $table->index('user_id');
        });

        Schema::table('porcionamento_itens', function (Blueprint $table){
            $table->index('porcionamento_id');
        });

        Schema::table('users', function (Blueprint $table){
            $table->index('group_id');
        });

        Schema::table('user_group_user_role', function (Blueprint $table){
            $table->index('user_group_id');
            $table->index('user_role_id');
        });

        Schema::table('porcionamentos', function (Blueprint $table){
            $table->index('usuario_autorizador_id');
            $table->index('justificativa_id');
        });

        Schema::table('colibri_nfc_pagamentos', function (Blueprint $table){
            $table->index('colibri_nfc_id');
        });

        Schema::table('erros', function (Blueprint $table){
            $table->index('model_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nfc', function (Blueprint $table){
            $table->dropIndex(['pv_id']);
        });

        Schema::table('nfc_itens', function (Blueprint $table){
            $table->dropIndex(['nfc_id']);
        });

        Schema::table('porcionamentos', function (Blueprint $table){
            $table->dropIndex(['user_id']);
        });

        Schema::table('porcionamento_itens', function (Blueprint $table){
            $table->dropIndex(['porcionamento_id']);
        });

        Schema::table('users', function (Blueprint $table){
            $table->dropIndex(['group_id']);
        });

        Schema::table('user_group_user_role', function (Blueprint $table){
            $table->dropIndex(['user_group_id']);
            $table->dropIndex(['user_role_id']);
        });

        Schema::table('porcionamentos', function (Blueprint $table){
            $table->dropIndex(['usuario_autorizador_id']);
            $table->dropIndex(['justificativa_id']);
        });

        Schema::table('colibri_nfc_pagamentos', function (Blueprint $table){
            $table->dropIndex(['colibri_nfc_id']);
        });

        Schema::table('erros', function (Blueprint $table){
            $table->dropIndex(['model_id']);
        });
    }
}
