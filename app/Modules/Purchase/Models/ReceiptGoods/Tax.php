<?php

namespace App\Modules\Purchase\Models\ReceiptGoods;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Purchase\Models\ReceiptGoods\receiptGoodsTax
 *
 * @property int $id
 * @property string $idReceiptGoodsItems
 * @property int|null $seqCode
 * @property int|null $sequenceSerial
 * @property string $seriesStr
 * @property string $subStr
 * @property string $sequenceModel
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\receiptGoodsTax whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\receiptGoodsTax whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\receiptGoodsTax whereIdReceiptGoodsItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\receiptGoodsTax whereSeqCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\receiptGoodsTax whereSequenceModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\receiptGoodsTax whereSequenceSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\receiptGoodsTax whereSeriesStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\receiptGoodsTax whereSubStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\receiptGoodsTax whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $idReceiptGoods
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Tax whereIdReceiptGoods($value)
 * @property string|null $NFEKey
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Tax newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Tax newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Tax query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Tax whereNFEKey($value)
 */
class Tax extends Model
{
    protected $table = 'receipt_goods_taxes';

    public function saveInDB($request, $id){
      $this->idReceiptGoods = $id;
      $this->seqCode = $request->type_tax;
      $this->sequenceSerial = $request->number_nf;
      $this->seriesStr = $request->serie;
      $this->subStr = $request->sserie;
      $this->sequenceModel = $request->model;
      $this->NFEKey = $request->key_NFE;
      $this->save();
    }
}
