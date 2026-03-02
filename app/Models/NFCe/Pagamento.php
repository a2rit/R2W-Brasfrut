<?php

namespace App\Models\NFCe;

use App\ErrorTrait;
use App\Models\NFCe;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    use ErrorTrait;

    protected $table = "nfc_pagamentos";

    public function nfce()
    {
        return $this->belongsTo(NFCe::class, "nfc_id", "id");
    }

    public function getNomeAttribute(): string
    {
        $tipos = [
            1  => '01 - Dinheiro',
            2  => '02 - Cheque',
            3  => '03 - Cartão de Crédito',
            4  => '04 - Cartão de Débito',
            5  => '05 - Crédito Loja',
            10 => '10 - Vale Alimentação',
            11 => '11 - Vale Refeição',
            12 => '12 - Vale Presente',
            13 => '13 - Vale Combustível',
            15 => '15 - Boleto Bancário',
            16 => '16 - Depósito Bancário',
            17 => '17 - Pagamento Instantâneo (PIX)',
            18 => '18 - Transferência bancária, Carteira Digital',
            19 => '19 - Programa de fidelidade, Cashback, Crédito Virtual',
            90 => '90 - Sem pagamento',
            99 => '99 - Outros',
        ];

        return $tipos[(int)$this->tipo] ?? 'Desconhecido';
    }
}
