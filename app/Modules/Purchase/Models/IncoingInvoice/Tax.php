<?php

namespace App\Modules\Purchase\Models\IncoingInvoice;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Purchase\Models\IncoingInvoice\Tax
 *
 * @property int $id
 * @property string $idIncoingInvoice
 * @property int|null $seqCode
 * @property int|null $sequenceSerial
 * @property string|null $seriesStr
 * @property string|null $subStr
 * @property string|null $sequenceModel
 * @property string|null $NFEKey
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax whereIdIncoingInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax whereNFEKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax whereSeqCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax whereSequenceModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax whereSequenceSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax whereSeriesStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax whereSubStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Tax whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tax extends Model
{
    protected $table = 'incoing_invoice_taxes';
    
    public function saveInDB($request, $id){
        $this->idIncoingInvoice = $id;
        $this->seqCode = $request->type_tax;
        $this->sequenceSerial = $request->number_nf;
        $this->seriesStr = $request->serie;
        $this->subStr = $request->sserie;
        $this->sequenceModel = $request->model;
        // $this->NFEKey = $request->key_NFE;
        $this->save();
    }

    public function duplicate($value, $idIncoingInvoice){

        $this->idIncoingInvoice = $idIncoingInvoice;
        $this->seqCode = $value->seqCode;
        $this->sequenceSerial = $value->sequenceSerial;
        $this->seriesStr = $value->seriesStr;
        $this->subStr = $value->subStr;
        $this->sequenceModel = $value->sequenceModel;
        // $this->NFEKey = $value->NFEKey;
        $this->save();
    }
}
