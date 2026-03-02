<?php

namespace App\Modules\Inventory\Models\item;

use Illuminate\Database\Eloquent\Model;

class Approve extends Model
{

    protected $table = 'items_approves';
    protected $fillable = [
        'itemCode', 'needApproval'
    ];

    public function saveInDB($request){

        $item_approval = $this::where('itemCode', $request->itemCode)->first();
        if(isset($request->needApproval) && $request->needApproval == 'on'){
            if(!empty($item_approval)){
                $item_approval->needApproval = 'Y';
                $item_approval->save();
            }else{
                $this::create(['itemCode'=>$request->itemCode, 'needApproval' => 'Y']);
            }
        }else if(!empty($item_approval)){
            $item_approval->needApproval = 'N';
            $item_approval->save();
        }
    }

}