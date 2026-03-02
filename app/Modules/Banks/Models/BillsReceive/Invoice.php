<?php

namespace App\Modules\Banks\Models\BillsReceive;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Banks\Models\BillsReceive\Invoice
 *
 * @property int $id
 * @property string|null $idBillsReceive
 * @property string|null $codSAP
 * @property string|null $installmentId
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereIdBillsReceive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereInstallmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $docEntry
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereDocEntry($value)
 * @property string $type
 * @property int $docNum
 * @property string $docDate
 * @property string $dueDate
 * @property string|null $serial
 * @property string|null $parcel
 * @property float $lineSum
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereDocNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereLineSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereParcel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Invoice whereType($value)
 */
class Invoice extends Model
{
    protected $table = 'bills_receive_invoices';

    public function saveInDB($value,$id){
      $this->idBillsReceive = $id;
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
