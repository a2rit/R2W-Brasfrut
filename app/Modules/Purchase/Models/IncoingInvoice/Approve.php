<?php

namespace App\Modules\Purchase\Models\IncoingInvoice;

use App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice;
use  App\Modules\Settings\Models\Lofted;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Purchase\Models\PurchaseOrder\Approve
 *
 * @property int $id
 * @property string $idIncoingInvoice
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereidIncoingInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Approve whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Approve extends Model
{
    protected $table = 'incoing_invoice_approves';
    protected $fillable = ['idIncoingInvoice','idLofted','idApproverDocuments','idUser','status'];

    const STATUS_OPEN = 1,
          STATUS_CLOSE = 0;

    public function validUserApproved($idIncoingInvoice){
        try {
            $approve =  Approve::where('idIncoingInvoice', '=', $idIncoingInvoice)
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

    public function getUserApprove($idIncoingInvoice){
        return Approve::join('users', 'users.id', '=', 'incoing_invoice_approves.idUser')
                         ->join('incoing_invoices','incoing_invoices.id','=', 'incoing_invoice_approves.idIncoingInvoice')
                         ->where('incoing_invoices.id','=',$idIncoingInvoice)
                         ->get(['users.name']);
    }

    public function validApprover($id,$idIncoingInvoice){
        $query =  Approve::where('idUser',$id)
        ->where('idIncoingInvoice',$idIncoingInvoice)
        ->get();

        //Se já existir aprovação desse usuário para o pedido, retorna false e ele não pode mais aprovar
        if($query->isNotEmpty()){
            return false;
        //Se não existir aprovação do usuário para o pedido, retorna true e ele pode aprovar
        }else{
            return true;
        }
    }

    public function validApproverNivel($idIncoingInvoice){

       
        $invoice = IncoingInvoice::find($idIncoingInvoice);
            
        $qtd =  Approve::join('incoing_invoices','incoing_invoices.id', '=', 'incoing_invoice_approves.idIncoingInvoice')
                         ->where('incoing_invoice_approves.idIncoingInvoice', '=', $idIncoingInvoice)
                         ->count('incoing_invoice_approves.id');

        $query = DB::select('select * from approver_documents where approverUser = ? and idLoftedApproveds = ? ', [auth()->user()->id,$invoice->idLofted]);

        if($query[0]->nivel == $qtd+1){
            return true;
        }else{
            return false;
        }
        
    }
}
