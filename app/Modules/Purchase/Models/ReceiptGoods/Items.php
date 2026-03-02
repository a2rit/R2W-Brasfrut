<?php
namespace App\Modules\Purchase\Models\ReceiptGoods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\Partners\Models\Partner\Catalog;
use Illuminate\Database\Eloquent\Model;
use App\LogsError;
/**
 * App\receiptGoodsItems
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idReceiptGoods
 * @property string $codSAP
 * @property string $price
 * @property string $quantity
 * @property string $codUse
 * @property string $codProject
 * @property string|null $codCFOP
 * @property string $codCost
 * @property string|null $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems whereCodCFOP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems whereCodCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems whereCodProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems whereCodUse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems whereIdReceiptGoods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsItems whereUpdatedAt($value)
 * @property string $itemCode
 * @property float $lineSum
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Items whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Items whereLineSum($value)
 * @property string|null $itemName
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Items whereItemName($value)
 * @property string|null $taxCode
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Items newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Items newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Items query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Items whereTaxCode($value)
 */
class Items extends Model
{ 
    const STATUS_CLOSE = 0;
    const STATUS_OPEN = 1;
    const STATUS_CANCEL = 2;
    
    protected $table = 'receipt_goods_items';

    public function saveInDB($value, $id){
      try {
        $this->idReceiptGoods = $id;
        if(isset($value['codSAP'])){
          $this->itemCode = $value['codSAP'];
        }else{
          $this->itemCode = $this->getItemCode($value['codPartners']);
        }
        $this->itemName = $value['itemName'];
        $this->price = clearNumberDouble($value['preco']);
        $this->quantity = clearNumberDouble($value['qtd']);
        $this->codUse = $value['use'];
        $this->codProject = $value['projeto'];
        $this->codCost = $value['role'];
        $this->codCFOP = $value['cfop'];
        $this->taxCode = $value['taxCode'];
        $this->status = self::STATUS_OPEN;
        $this->lineSum = (clearNumberDouble($value['preco']) * clearNumberDouble($value['qtd']));
        $this->save();
      } catch (\Throwable $e) {
        $logsError = new LogsError();
        $logsError->saveInDB('E0104E',$e->getFile().' | '.$e->getLine(),$e->getMessage());
      }
    }
    private function getItemCode($codPartner){
      try {
          return Catalog::where('substitute', '=', $codPartner)->select('itemCode')->first()->itemCode;
      } catch (\Exception $e) {
        $logsError = new LogsError();
        $logsError->saveInDB('E01FE',$e->getFile().' | '.$e->getLine(),$e->getMessage());
      }

    }

    public function getUtilizationLabel(){
      try {
        $sap = new Company(false);
        return $sap->query("SELECT T0.[Usage] FROM OUSG T0 WHERE T0.[ID]  = '{$this->codUse}'")[0]['Usage'];
      } catch (\Exception $e) {
        $logsError = new logsError();
        $logsError->saveInDB('E036F',$e->getFile().' | '.$e->getLine(), $e->getMessage());
        return false;
      }
    }
}
