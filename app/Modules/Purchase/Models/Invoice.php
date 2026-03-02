<?php

namespace App\Modules\Purchase\Models;

use App\Modules\Purchase\Models\Invoice\Item;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Purchase\Models\Invoice
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $issuer_id
 * @property string $key
 * @property int $number
 * @property string $series
 * @property string $issue_date
 * @property float $total_value
 * @property float $products_value
 * @property float $freight_value
 * @property float $secure_value
 * @property float $other_values
 * @property float $discount_value
 * @property string|null $info
 * @property string $xml
 * @property string|null $observations
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Modules\Purchase\Models\Issuer $issuer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Purchase\Models\Invoice\Item[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereDiscountValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereFreightValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereIssuerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereObservations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereOtherValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereProductsValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereSecureValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereSeries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereTotalValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice whereXml($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice query()
 */
class Invoice extends Model
{
    protected $table = 'purchase_invoices';

    protected $casts = [
        'observations' => 'json'
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'invoice_id', 'id');
    }

    public function issuer()
    {
        return $this->belongsTo(Issuer::class, 'issuer_id', 'id');
    }

    /**
     * @param string $key
     * @return Invoice|null
     */
    public static function findByKey(string $key)
    {
        return Invoice::whereKey($key)->first();
    }

    /**
     * @param string $file
     * @throws \Exception
     * @throws \Throwable
     */
    public function loadXml(string $file)
    {
        $xml = simplexml_load_string($file);

        if ($xml->getName() !== "nfeProc" || !isset($xml->NFe) || isset($xml->protNFe->infProt->chNFe)) {
            throw new \Exception("Arquivo inválido!");
        }

        $key = (string)$xml->protNFe->infProt->chNFe;
        if(self::findByKey($key)) {
            throw new \Exception("NFe já importada!");
        }

        $infNFe = $xml->NFe->infNFe;

        if((int)$infNFe->ide->finNFe !== 1) {
            throw new \Exception("Esta NFe não é de venda!");
        }

        $result = \DB::transaction(function () use ($file, $infNFe, $key, $xml) {
            $_invoice = $infNFe->ide;

            $issuer = Issuer::updateOrCreateByXml($infNFe->emit);

            $invoice = new Invoice();
            $invoice->issuer_id = $issuer->id;
            $invoice->series = $_invoice->serie;
            $invoice->number = $_invoice->nNF;
            $invoice->key = $key;
            $invoice->issue_date = $_invoice->dhEmi;

            /** @var \SimpleXMLElement $total */
            $total = $infNFe->total->ICMSTot;
            $invoice->total_value = $total->vNF;
            $invoice->freight_value = $total->vFrete;
            $invoice->discount_value = $total->vDesc;
            $invoice->products_value = $total->vProd;
            $invoice->secure_value = $total->vSeg;
            $invoice->xml = $file;
            $invoice->info = $infNFe->infAdic->infCpl;

            $observations = [];

            foreach ($infNFe->infAdic->obsCont as $obs) {
                $attributes = $obs->attributes();
                $observations[(string)$attributes["xCampo"]] = (string)$obs->xTexto;
            }
            $invoice->observations = $observations;
            $invoice->save();

            foreach ($infNFe->det as $_item) {
                $item = new Item();
                $item->invoice_id = $invoice->id;

            }


        });

    }
}
