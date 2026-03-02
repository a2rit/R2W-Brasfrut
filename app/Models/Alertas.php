<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alertas extends Model
{
    protected $table = 'alertas';
    protected $fillable = ['id_document','type_document','id_user','title','text','status'];

    const  
    OTRANSFERTAKING = 1,
    OPUCHASEREQUEST = 2,
    OPURCHASEORDER = 3;

    const STATUS_OPEN = 1;
    const STATUS_READ = 0;

    const DOCUMENT_BASE = [
        '1' => 'Pedido de Transferencia',
        '2' => 'Solicitacao de compra',
        '3' => 'Pedido de Compra',
    ];

    public function getUrl($type){

        switch($type){
            case 1: 
                return "inventory.transferTaking.edit";
                break;
            case 2: 
                return "purchase.request.read";
                break;
            case 3: 
                return "purchase.order.read";
                break;

            default:
                return '';
                break;
        }
    }

    public static function checkAlerts($id_document)
    {
        self::where('id_document', $id_document)->where('id_user', auth()->user()->id)->where('status', 1)->update(['status' => Alertas::STATUS_READ]);
    }
}
