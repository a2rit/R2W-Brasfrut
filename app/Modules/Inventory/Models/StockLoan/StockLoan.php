<?php

namespace App\Modules\Inventory\Models\StockLoan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Modules\Inventory\Models\StockLoan\Item as Items;
use App\Modules\Inventory\Models\StockLoan\StockLoan;
use App\Modules\Inventory\Models\StockLoan\Historic;
use App\Upload;
use App\LogsError;
use App\User;
use Litiano\Sap\NewCompany;

use Litiano\Sap\Enum\BoObjectTypes;

/**
 * 
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idUser
 * @property string $date
 * @property string $code
 * @property string $toWhs
 * @property string $fromWhs
 * @property string $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereFromWhs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereToWhs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereUpdatedAt($value)
 * @property string $docDate
 * @property string $taxDate
 * @property string|null $codSAP
 * @property string $fromWarehouse
 * @property string $toWarehouse
 * @property string|null $comments
 * @property bool $is_locked
 * @property string|null $message
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereFromWarehouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereTaxDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereToWarehouse($value)
 * @property string|null $dbUpdate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan whereDbUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\StockLoan query()
 */
class StockLoan extends Model
{
    protected $table = 'stock_loans';

    const STATUS_OPEN = 1;
    const STATUS_CANCEL = 2;
    const STATUS_PENDING = 3;
    const STATUS_CLOSED = 4;

    const TEXT_STATUS = [
        '1' => 'Aberto',
        '2' => 'Cancelado',
        '3' => 'Pendente',
        '4' => 'Fechado'
    ];


    public function saveInDB(Request $request)
    {
        
        try {
            $this->idUser = auth()->user()->id;
            $this->code = $this->createCode();
            $this->docDate = DATE('Y-m-d');
            $this->taxDate = $request->data;
            $this->fromWarehouse = $request->fromWarehouse;
            $this->toWarehouse = $request->toWarehouse;
            $this->comments = mb_convert_encoding((String)$request->comments, 'UTF-8');
            $this->requester = $request->requester; 
            $this->is_locked = false;
            $this->status = self::STATUS_OPEN;
            if ($this->save()){
                
                foreach ($request->requiredProducts as $key => $value) {
                    $item = new Items();
                    $value['quantityDevolved'] = '0';
                    $value['quantityPending'] = '0';
                    $item->saveInDB($value, $this->id);
                }
            }
            
        } catch (\Throwable $e) {
            $this->is_locked = true;
            $this->message = ($e->getMessage());
            $logsError = new LogsError();
            $logsError->saveInDB("E0036", "cadastro de emprestimo de estoque na WEB", $e->getMessage());
            return ['type' => 'error', 'message' => $e->getMessage()];
        }

    }
    public function devolutionInDB(Request $request)
    {
        try {
            
            $stockLoan = StockLoan::find($request->stockLoan_id);
            // dd($request);
            // $stockLoan->idUser = auth()->user()->id;
            // $stockLoan->docDate = $stockLoan->docDate;
            if($stockLoan->devolution == '0'){
                $stockLoan->taxDate = $request->data;
                $stockLoan->returner = $request->returner; 
            }
            // $stockLoan->code = $stockLoan->createCode();
            $stockLoan->fromWarehouse = '26'; //Session::get('fomWarehouse');
            $stockLoan->toWarehouse = '06'; //Session::get('toWarehouse');
            // $stockLoan->requester = $stockLoan->requester; 
            // dd($request);
            // // $stockLoan->id_stockLoan = $request->stockLoan_id; 
            // $stockLoan->comments = 'Devolução de emprestimo: '.$stockLoan->code.'. Observações: '.$request->commentsD;
            $stockLoan->is_locked = false;
            $stockLoan->devolution = true;
            $parcial = [];
            saveUpload($request,'stockLoans',$stockLoan->id);
            if ($stockLoan->save()) {
                $cont = 1;
                // dd($request);
                // foreach ($stockLoan->getItens($stockLoan->id) as $value) {
                foreach ($request->get('items')  as $key => $value) {
                
                    $item = Items::where('id',$key)->get()->first();              
                    $parcial[$cont] = $item->saveInDB((Array)$value, $stockLoan->id);
                    $cont++;
                 
                }
                
                if(array_search('1',$parcial)){
                    $stockLoan->parcial = '1';
                }else{
                    $stockLoan->parcial = '0';
                }
                
                
            } else {
                return ['type' => 'error', 'message' => 'contate o suporte erro na validação dos dados'];
            }

            $stockLoan->devolved = true;
            $stockLoan->save();

            return $stockLoan;
            
        } catch (\Throwable $e) {
            $stockLoan->is_locked = true;
            $stockLoan->message = ($e->getMessage());
            $logsError = new LogsError();
            $logsError->saveInDB("E0037", "devolução de emprestimo de estoque na WEB", $e->getMessage());
            return ['type' => 'error', 'message' => $e->getMessage()];
        }

    }

    public function saveInSAP($obj)
    {
        try {
           
            try {
                $sap = NewCompany::getInstance()->getCompany();
            } catch (\Exception $e) {
                $obj->message = $e->getMessage();
                $obj->save();
                throw $e;
            }
            
            $item = $sap->GetBusinessObject(BoObjectTypes::oStockTransfer);
            $item->DocDate = $obj->docDate;
            $item->TaxDate = $obj->taxDate;
            $item->FromWarehouse = $obj->devolved ? $obj->toWarehouse : $obj->fromWarehouse;
            $item->ToWarehouse = $obj->devolved ? $obj->fromWarehouse : $obj->toWarehouse;
            $item->Comments = (String)$obj->comments;
            $item->JournalMemo = 'Empréstimo Código WEB: '.$obj->code;
            $item->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
            $item->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($obj->idUser);
            
            $j = 0;
            $historicIds = [];
            foreach ($obj->getItens($obj->id) as $key => $value) {
                $quantity = 0;
                foreach(Historic::where('idItem', $value->id)->where('idStockLoan', $obj->id)->where('status', 0)->get() as $register){
                    $quantity += $register->quantityServed;
                    array_push($historicIds, $register->id);
                }

                if($quantity > 0){
                    $item->Lines->SetCurrentLine($j);
                    $item->Lines->ItemCode = (String)$value->itemCode;
                    $item->Lines->Quantity = (Double)$quantity;
                    $item->Lines->Add();
                    $j++;   
                }
                
            }
            
            if ($item->Add() !== 0) {
                $obj->message = $sap->GetLastErrorDescription();
                $obj->is_locked = true;
                $obj->dbUpdate = 0;
                $obj->save();
            } else {
                // $obj->codSAP = $sap->GetNewObjectKey();
                $obj->codSAP = $obj->codSAP.'/'.$sap->GetNewObjectKey();
                $obj->message = "Item salvo no SAP com sucesso.";
                $obj->is_locked = false;
                $obj->dbUpdate = 0;
                $obj->save();
                Historic::whereIn('id', $historicIds)->update(['status' => 1]);
            }
        } catch (\Exception $e) {
            $LogsError = new LogsError();
            $LogsError->saveInDB('E0101', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            $obj->message = $e->getMessage();
            $obj->is_locked = true;
            $obj->dbUpdate = 0;
            $obj->save();
        }

    }

    private function createCode()
    {
        $busca = DB::select("select top 1 stock_loans.code from stock_loans order by stock_loans.id desc");
        
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'SL00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }

    public function getNameUser($id)
    {
        return StockLoan::join('users', 'users.id', '=', 'stock_loans.idUser')
            ->where('users.id', '=', $id)
            ->select('users.name')
            ->get()[0]->name;
    }

    public function getItens($id)
    {
        return DB::SELECT("SELECT T0.id,T0.quantityDevolved, T0.itemCode, T0.quantity, T0.projectCode, T0.distributionRule, T0.costCenter, T0.costCenter2 FROM stock_loans_items T0 join stock_loans T1 on T0.idStockLoan = T1.id WHERE T1.id = '{$id}'");
    }
}
