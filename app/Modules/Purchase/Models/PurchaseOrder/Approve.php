<?php

namespace App\Modules\Purchase\Models\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use  App\Modules\Settings\Models\Lofted;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Purchase\Models\PurchaseOrder\Approve
 *
 * @property int $id
 * @property string $idPurchaseOrder
 * @property string $idLofted
 * @property string $idApproverDocuments
 * @property string $idUser
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereIdApproverDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereIdLofted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereIdPurchaseOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Approve extends Model
{
    protected $table = 'purchase_order_approves';
    protected $fillable = ['idPurchaseOrder','idLofted','idApproverDocuments','idUser','status'];

    const STATUS_OPEN = 1,
          STATUS_CLOSE = 0;

    public function validUserPurcharseApproved($idPurchaseOrder){
        try {
            $approve =  Approve::where('idPurchaseOrder', '=', $idPurchaseOrder)
            ->where('idUser', '=', Auth::user()->id)
            ->where('status', '=', 0)
            ->first();

            if(!is_null($approve)){
                return true;
            } else {
                return false;
            }


        } catch (\Throwable $th) {
            return false;
        }

    }

    public function getUserApprove($idPurchaseOrder){
        return Approve::join('users', 'users.id', '=', 'purchase_order_approves.idUser')
                         ->join('purchase_orders','purchase_orders.id','=', 'purchase_order_approves.idPurchaseOrder')
                         ->where('purchase_orders.id','=',$idPurchaseOrder)
                         ->get(['users.name']);
    }

    public function validApprover($id,$idPurchaseOrder){
        $query =  Approve::where('idUser',$id)
        ->where('idPurchaseOrder',$idPurchaseOrder)
        ->get();

        //Se já existir aprovação desse usuário para o pedido, retorna false e ele não pode mais aprovar
        if($query->isNotEmpty()){
            return false;
        //Se não existir aprovação do usuário para o pedido, retorna true e ele pode aprovar
        }else{
            return true;
        }
    }

    public function validApproverNivel($idPurchaseOrder){

       
        $opor = PurchaseOrder::find($idPurchaseOrder);
            
        $qtd =  Approve::join('purchase_orders','purchase_orders.id', '=', 'purchase_order_approves.idPurchaseOrder')
                         ->where('purchase_order_approves.idPurchaseOrder', '=', $idPurchaseOrder)
                         ->count('purchase_order_approves.id');

        $query = DB::select('select * from approver_documents where approverUser = ? and idLoftedApproveds = ? ', [auth()->user()->id,$opor->idLofted]);

        if($query[0]->nivel == $qtd+1){
            return true;
        }else{
            return false;
        }
        
    }
}
