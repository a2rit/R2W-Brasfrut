<?php

namespace  App\Modules\Banks\Models\BillsPay;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Banks\Models\BillsPay\Invoice
 *
 * @property int $id
 * @property string $idBillsPay
 * @property int $docEntry
 * @property int|null $installmentId
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereDocEntry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereIdBillsPay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereInstallmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $type
 * @property int $docNum
 * @property string $docDate
 * @property string $dueDate
 * @property string|null $serial
 * @property string|null $parcel
 * @property float $lineSum
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereDocNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereLineSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereParcel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\Invoice whereType($value)
 */
class Invoice extends Model
{
    protected $table = 'bills_pay_invoices';

    public function saveInDB($value,$id){
      $this->idBillsPay = $id;
      $this->type = $value['type'];
      $this->docEntry = $value['docEntry'];
      $this->docNum = $value['docNum'];
      $this->docDate = $value['docDate'];
      $this->dueDate = $value['dueDate'];
      $this->serial = $value['serial'];
      $this->installmentId = $value['installmentId'];
      $this->parcel = $value['parcel'];
      $this->lineSum = $value['lineSum'];
      $this->description = $value['description'];
      $this->save();
    }
}
