<?php

namespace App\Models\Colibri;

use App\ErrorTrait;
use App\Exceptions\SapIntegrationException;
use App\Models\PontoVenda;
use Carbon\Carbon;
use DB;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Litiano\Sap\NewCompany;
use Throwable;

/**
 * App\Models\Colibri\Pagamento
 *
 * @property int $id
 * @property string $colibri_nfc_id
 * @property string $codigo_unico
 * @property string $descricao
 * @property float $valor
 * @property string $numero_autorizacao
 * @property float|null $gorjeta
 * @property int|null $gorjeta_codigo_sap
 * @property int|null $pv_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read NFc $nfc
 * @method static Builder|Pagamento whereCodigoUnico($value)
 * @method static Builder|Pagamento whereColibriNfcId($value)
 * @method static Builder|Pagamento whereCreatedAt($value)
 * @method static Builder|Pagamento whereDescricao($value)
 * @method static Builder|Pagamento whereErroId($value)
 * @method static Builder|Pagamento whereGorjeta($value)
 * @method static Builder|Pagamento whereGorjetaCodigoSap($value)
 * @method static Builder|Pagamento whereId($value)
 * @method static Builder|Pagamento whereNumeroAutorizacao($value)
 * @method static Builder|Pagamento wherePvId($value)
 * @method static Builder|Pagamento whereUpdatedAt($value)
 * @method static Builder|Pagamento whereValor($value)
 * @mixin Eloquent
 * @method static Builder|Pagamento newModelQuery()
 * @method static Builder|Pagamento newQuery()
 * @method static Builder|Pagamento query()
 */
class Pagamento extends Model
{
    use ErrorTrait;

    protected $table = 'colibri_nfc_pagamentos';

    /**
     * @param $xml
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public function loadXml($xml)
    {
        return DB::transaction(function () use ($xml) {
            if (isset($xml->{"fiscal.comprovantemeios"})) {
                $tag = "fiscal.comprovantemeios";
            } elseif (isset($xml->{"fiscal.comprovante_meio"})) {
                $tag = "fiscal.comprovante_meio";
            } else {
                throw new Exception("Tag não encontrada!");
            }

            foreach ($xml->{$tag}->{$tag} as $item) {
                if ((string)$item["comprovante_id"] == "") {
                    continue;
                }
                $codigoUnico = (string)$item["comprovante_id"] . "-" . (string)$item["detalhe_id"];
                $pagColibri = Pagamento::where('codigo_unico', '=', $codigoUnico)->first();
                if (!$pagColibri) {
                    $pagColibri = new Pagamento();
                }
                $pagColibri->colibri_nfc_id = (string)$item["comprovante_id"];
                $pagColibri->valor = str_replace(",", ".", (string)$item["valor"]);
                $pagColibri->descricao = (string)$item["descricao"];
                $pagColibri->numero_autorizacao = (string)$item["num_autorizacao"];
                $pagColibri->codigo_unico = $codigoUnico;
                $pagColibri->save();
            }
            return true;
        });
    }

    /**
     * @throws SapIntegrationException
     */
    public function salvarGorjetasSap(): bool
    {
        $sap = NewCompany::getInstance()->getCompany();
        $pv = PontoVenda::find($this->pv_id);
        $conta = $sap->GetBusinessObject(24); //oIncomingPayments
        $conta->DocType = 1;//rAccount
        $conta->AccountPayments->AccountCode = $pv->conta_servicos;//rAccount
        $conta->AccountPayments->SumPaid = (double)$this->gorjeta;
        $data = $this->nfc->emissao->format("d/m/Y");
        $conta->DocDate = $data;
        $conta->TaxDate = $data;

        $dinheiro = 0;

        switch ($this->descricao) {
            case "DINHEIRO":
                $dinheiro += $this->valor;
                break;
            case "CRED CARD":
                $cartao = json_decode($pv->cred_card, true);
                break;
            case "VISA":
                $cartao = json_decode($pv->visa, true);
                break;
            case "VISA ELECTRON":
                $cartao = json_decode($pv->visa_electron, true);
                break;
            case "AMEX":
                $cartao = json_decode($pv->amex, true);
                break;
            default:
                $cartao = json_decode($pv->outro_cartao, true);
                break;
        }
        if (isset($cartao)) {
            $conta->CreditCards->CardValidUntil = date('d/m/y');
            $conta->CreditCards->CreditCard = (int)$cartao["CreditCard"];
            $conta->CreditCards->CreditCardNumber = "1234567890";
            $conta->CreditCards->NumOfCreditPayments = 1;
            $conta->CreditCards->CreditSum = (double)$this->gorjeta;
            $conta->CreditCards->AdditionalPaymentSum = (double)$this->gorjeta;
            if ($this->num_autorizacao != "") {
                $conta->CreditCards->VoucherNum = $this->num_autorizacao;
            } else {
                $conta->CreditCards->VoucherNum = "012345";
            }
            $conta->CreditCards->PaymentMethodCode = (int)$cartao["CrTypeCode"];
            $conta->CreditCards->Add();
            $cartao = null;
        }

        if ($dinheiro > 0) {
            $conta->CashSum = (double)$dinheiro;
        }

        if ($conta->Add() != "0") {
            throw new SapIntegrationException(
                "Erro ao adicionar pagamentos de serviços. " . $sap->GetLastErrorDescription(),
                $sap->GetLastErrorCode()
            );
        }

        $this->destroyError();

        $this->gorjeta_codigo_sap = $sap->GetNewObjectKey();
        $this->save();

        return true;
    }

    public function nfc()
    {
        return $this->belongsTo(NFc::class, "colibri_nfc_id", "colibri_id");
    }
}
