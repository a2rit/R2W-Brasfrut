<?php

use App\Modules\Settings\Models\Company as DBCompany;
use Litiano\Sap\Company;
use App\Models\Alertas;
use App\Upload;
use Carbon\Carbon;

// use Auth;


function getAlertas(){
  return Alertas::select('type_document', DB::raw('count(id) as total'))
    ->where('id_user', auth()->user()->id)
    ->where("created_at", ">", Carbon::now()->subMonths(3))
    ->where('status', '=', '1')
    ->groupBy('type_document')
    ->pluck('total', 'type_document');
}

function isActiveRoute($route, $output = 'active')
{
    if (Route::currentRouteName() == $route) {
        return $output;
    }
}

function isActiveModule($route, $output = 'active')
{
    if (strpos(Route::current()->action['prefix'], $route) !== false) {
        return $output;
    }
}
function workCashFlow(){
  try {
      $busca = App\Modules\Settings\Models\Config::where('code','=','cashFlow')->get(['value']);
    if($busca[0]->value == '1'){
      return true;
    }else{
      return false;
    }
  } catch (\Throwable $th) {
    return false;
  }
}

function workCoin(){
  try {
    
    $busca = App\Modules\Settings\Models\Config::where('code','=','coin')->get(['value']);
    if($busca[0]->value == '1'){
      return true;
    }else{
      return false;
    }
  } catch (\Throwable $th) {
    return false;
  }
}
function workQuotation(){
  try {
    $busca = App\Modules\Settings\Models\Config::where('code','=','quotation')->get(['value']);
    if($busca[0]->value == '1'){
      return true;
    }else{
      return false;
    }
  } catch (\Throwable $th) {
    return false;
  }
}

function formatDate($data){
  if($data == null) return false;
  if(strlen($data) <= 10){
    $parteData = explode("-", $data);
    return $parteData[2] ."/". $parteData[1] ."/". $parteData[0];
  }else{
      $data = substr($data, 0,10);
      $parteData = explode("-", $data);
      return $parteData[2] ."/". $parteData[1] ."/". $parteData[0];
  }
}
function getDocNumTransfer($code){
  $transfer = DB::table('transfers')->select('codSAP')->where('code', $code)->first();

  if(!empty($transfer->codSAP)){
    return $transfer->codSAP;
  }else{
    return '';
  }
}
function formatDateUSA($data){
  $parteData = explode("/", $data);
  return $parteData[1] ."-". $parteData[0] ."-". $parteData[2];
}
function date_USA($_date = null) {
  $format = '/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/';
  if ($_date != null && preg_match($format, $_date, $partes)) {
  	return $partes[1].'-'.$partes[2].'-'.$partes[3];
  }
}
function dateToUSA($date) {
  $format = '/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/';
  if ($date != null && preg_match($format, $date, $partes)) {
  	return $partes[3].'-'.$partes[2].'-'.$partes[1];
  }
}
function saveUpload($collection, $table, $input, $origin = "R2W"){
  if ($origin == 'R2W' && $collection->hasFile('input-file-preview')) {
    try {
      $files = $collection->file('input-file-preview');

      foreach($files as $file){
          $upload = new App\Upload;
          $fileName = str_random(5)."-".date('his')."-".str_random(3)."=".$file->getClientOriginalName();
          $folderpath  = public_path('/uploads'.'/'.$table.'/item-'.$input);
          $file->move($folderpath , $fileName);
          $upload->idUser = auth()->user()->id;
          $upload->reference = $table;
          $upload->idReference = $input;
          $upload->diretory = '/uploads'.'/'.$table.'/item-'.$input.'/'.$fileName;
          $upload->save();
          // App\Jobs\UploadsToSAP::dispatch($upload)->onQueue(App\Jobs\Queue::QUEUE_PURCHASE_ORDERS);
          // $upload->saveInSAP($fileName, $folderpath, $upload);
      }
    } catch (\Exception $e) {
      $logsErro = new \App\logsError();
      $logsErro->saveInDB('UPLD001', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }elseif($origin == 'SAP'){
    if(count($collection) > 0){
      Upload::where('idReference', '=', $input)->where("absEntry", $collection[0]->AbsEntry)->where("reference", "=", $table)->delete();
      foreach($collection as $file){
        $upload = new App\Upload;
        $targetPath = public_path()."\\uploads\\{$table}\\item-{$input}\\";
        if(!file_exists($targetPath)){
          mkdir($targetPath);
        }
        $fileName = $file->FileName.".{$file->FileExt}";
        if(!preg_match('/[=;]/', $file->FileName)){
          $fileName = str_random(5)."-".date('his')."-".str_random(3)."=".$file->FileName.".{$file->FileExt}";
        }
        $folderpath = $file->trgtPath . "\\{$file->FileName}.{$file->FileExt}";
        copy($folderpath, "{$targetPath}\\{$fileName}");
        $upload->idUser = auth()->user()->id;
        $upload->reference = $table;
        $upload->idReference = $input;
        $upload->absEntry = $file->AbsEntry;
        $upload->diretory = '/uploads'.'/'.$table.'/item-'.$input.'/'.$fileName;
        $upload->save();
      }
    }
  }
}

function getAdvanceProviderSAP($codSAP){
  if($codSAP){
    $sap = new Company(false);
    $adPayments = $sap->query("SELECT T0.[DocNum], T0.[DocDate], T0.[DocTotal], T0.[Comments], T0.[DpmAppl] FROM ODPO T0 
                                INNER JOIN DPO1 T1 ON T0.[DocNum] = T1.[DocEntry] 
                                WHERE T0.[DocNum] = '$codSAP'
                                AND T0.[CANCELED] = 'N'");
    if(!empty($adPayments)){
      return $adPayments[0];
    }else{
      return false;
    }
  }
}

function creatPaindSum($request){
  $total = 0;

  if(!is_null($request->requiredProducts)){
    foreach ($request->requiredProducts as $key => $value) {
      if(isset($value['qtd']))
        $total +=  (((float)$value['qtd']) * ((float) $value['qtd']));
      }
    }
  return $total;
}

function formatCNPJ($cnpj){
  $str = preg_replace("/([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{2})/", "$1.$2.$3/$4-$5", $cnpj);
  return $str;
}
function checkPayments($request, $type){
  switch ($type) {
    case '1'://dinheiro
      if(isset($request->total_dinheiro) && !empty($request->total_dinheiro) && ($request->total_dinheiro != '')){
          return true;
      }else{
        return false;
      }
      break;
    case '2'://debito
      $result = false;
      if(isset($request->dt_transferencia) && !empty($request->dt_transferencia) && ($request->dt_transferencia != '')){
          if(isset($request->total_transferencia) && !empty($request->total_transferencia) && ($request->total_transferencia != '')){
            if(isset($request->referencia_transferencia) && !empty($request->referencia_transferencia) && ($request->referencia_transferencia != '')){
              $result = true;
            }
          }
        }
        return $result;
      break;
    case '3'://credito
      $result = false;
      if(isset($request->name_cartao) && !empty($request->name_cartao) && ($request->name_cartao != '')){
        if(isset($request->parcelas_cartao) && !empty($request->parcelas_cartao) && ($request->parcelas_cartao != '')){
          if(isset($request->total_credito) && !empty($request->total_credito) && ($request->total_credito != '')){
            $result = true;
          }
        }
      }
      return $result;
      break;
    default:
      return false;
      break;
  }
}
function checkAccess($modulo){
  $encode = auth()->user()->permissions;
  $decode = json_decode($encode);
  switch ($modulo) {
    case 'configuration':
      return $decode->configuration;
      break;
    case 'config_boot':
      return $decode->config_boot;
      break;
    case 'config_sale_point':
      return $decode->config_sale_point;
      break;
    case 'config_users':
      return $decode->config_users;
      break;
    case 'config_approvers':
      return $decode->config_approvers;
      break;
    case 'config_users_group':
      return $decode->config_users_group;
      break;
    case 'config_whs_group':
      return $decode->config_whs_group;
      break;
    case 'erros':
      return $decode->erros;
      break;
    case 'nfcs':
      return $decode->nfcs;
      break;
    case 'portioning':
      return $decode->portioning;
      break;
    case 'portion_search':
      return $decode->portion_search;
      break;
    case 'portion_list':
      return $decode->portion_list;
      break;
    case 'portion_loss':
      return $decode->portion_loss;
      break;
    case 'portion_justify':
      return $decode->portion_justify;
      break;
    case 'portion_loss_justify':
      return $decode->portion_loss_justify;
      break;
    case 'intern_consumption':
      return $decode->intern_consumption;
      break;
    case 'intern_consumption_perdas':
      if(isset($decode->intern_consumption_perdas)){
        return $decode->intern_consumption_perdas;
      }else{
        return false;
      }
      break;
    case 'intern_consumption_eventos':
      if(isset($decode->intern_consumption_eventos)){
        return $decode->intern_consumption_eventos;
      }else{
        return false;
      }
      break;
    case 'inventoryx':
        return $decode->inventoryx;
        break;
    case 'inventory_request':
        return $decode->inventory_request;
        break;
    case 'inventory_input':
      return $decode->inventory_input;
      break;
    case 'inventory_output':
      return $decode->inventory_output;
      break;
    case 'inventory_transfer_taking':
      return $decode->inventory_transfer_taking;
      break;
    case 'inventory_transfer':
      return $decode->inventory_transfer;
      break;
    case 'inventory_stock_loan':
      return $decode->inventory_stock_loan;
      break;
    case 'inventory_items':
      if(isset($decode->inventory_items)){
        return $decode->inventory_items;
      }else{
        return false;
      }
      break;
    case 'inventory_items_new':
      if(isset($decode->inventory_items_new)){
        return $decode->inventory_items_new;
      }else{
        return false;
      }
      break;
    case 'inventory_items_edit':
      if(isset($decode->inventory_items_edit)){
        return $decode->inventory_items_edit;
      }else{
        return false;
      }
      break;
    case 'accounting':
      return $decode->accounting;
      break;
    case 'account_lcm':
      return $decode->account_lcm;
      break;
    case 'b_partners':
      return $decode->b_partners;
      break;
    case 'b_partner':
      return $decode->b_partner;
      break;
    case 'purchasex':
      return $decode->purchasex;
      break;
    case 'purchase_order':
      return $decode->purchase_order;
      break;
    case 'purchase_suggestion_order':
      if(isset($decode->purchase_suggestion_order)){
        return $decode->purchase_suggestion_order;
      }else{
        return false;
      }
      break;
    case 'purchase_request':
      return $decode->purchase_request;
      break;
    case 'purchase_suggestion_request':
      if(isset($decode->purchase_suggestion_request)){
        return $decode->purchase_suggestion_request;
      }else{
        return false;
      }
      break;
    case 'purchase_quotation':
      if(isset($decode->purchase_quotation)){
        return $decode->purchase_quotation;
      }else{
        return false;
      }
      break;
    case 'purchase_nfc':
      return $decode->purchase_nfc;
      break;
    case 'purchase_advance_provider':
      if(isset($decode->purchase_advance_provider)){
        return $decode->purchase_advance_provider;
      }else{
        return false;
      }
      break;
    case 'purchase_order_budget_relatory':
        if(isset($decode->purchase_order_budget_relatory)){
          return $decode->purchase_order_budget_relatory;
        }else{
          return false;
        }
        break;
    case 'tomticket':
      if(isset($decode->tomticket)){
        return $decode->tomticket;
      }else{
        return false;
      }
      break;
    case 'dashboard_menu':
      if(isset($decode->dashboard_menu)){
        return $decode->dashboard_menu;
      }else{
        return false;
      }
      break;
    case 'dashboard_purchase':
      if(isset($decode->dashboard_purchase)){
        return $decode->dashboard_purchase;
      }else{
        return false;
      }
      break;
    case 'dashboard_finances':
      if(isset($decode->dashboard_finances)){
        return $decode->dashboard_finances;
      }else{
        return false;
      }
      break;
    default:
      return false;
      break;
  }
}


function clearNumberDouble($string){
  
  $aux = str_replace('R$','',$string);
  $aux = str_replace('EUR','',$string);
  $aux = str_replace('.','',$aux);
  $aux = str_replace(',','.',$aux);
  return (Double) $aux;

}
function clearString($str){
  $LetraProibi = Array(",",".","'","\"","&","|","!","#","$","¨","*","(",")","`","´","<",">",";","=","+","§","{","}","[","]","^","~","?","%");
    $special = Array('Á','È','ô','Ç','á','è','Ò','ç','Â','Ë','ò','â','ë','Ø','Ñ','À','Ð','ø','ñ','à','ð','Õ','Å','õ','Ý','å','Í','Ö','ý','Ã','í','ö','ã',
       'Î','Ä','î','Ú','ä','Ì','ú','Æ','ì','Û','æ','Ï','û','ï','Ù','®','É','ù','©','é','Ó','Ü','Þ','Ê','ó','ü','þ','ê','Ô','ß','‘','’','‚','“','”','„');
    $clearspc = Array('a','e','o','c','a','e','o','c','a','e','o','a','e','o','n','a','d','o','n','a','o','o','a','o','y','a','i','o','y','a','i','o','a',
       'i','a','i','u','a','i','u','a','i','u','a','i','u','i','u','','e','u','c','e','o','u','p','e','o','u','b','e','o','b','','','','','','');
    $newStr = str_replace($special, $clearspc, $str);
    $newStr = str_replace($LetraProibi, "", trim($newStr));
    return strtoupper($newStr);
}

function getCoin(){
  try {
    
    $coin = DB::SELECT("SELECT value from settings WHERE code like 'SystemCoin' ")[0]->value;
    
    $date_atual = DATE('Y-m-d');
    return number_format(DB::SELECT("SELECT rate from currency_rates WHERE posting_date = '{$date_atual}' and coin = '{$coin}'")[0]->rate, 2,',','.');
  } catch (\Exception $e) {
    return '0,00';
  }
}
function compressText($string, $chars, $complements = true){
  if (strlen($string) > $chars)
        if($complements){
          return substr($string, 0, $chars).'...';
        }else{
          
         return substr($string, 0, $chars);
        }
     else
         return $string;
}

function mask($val, $mask){
 $maskared = '';
 $k = 0;
 for($i = 0; $i<=strlen($mask)-1; $i++){
   if($mask[$i] == '#'){
     if(isset($val[$k]))
     $maskared .= $val[$k++];
   }else{
     if(isset($mask[$i]))
     $maskared .= $mask[$i];
   }
 }
 return $maskared;
}


function getUserName($id){
  if(App\User::where('id',$id)->first()){
    return App\User::where('id',$id)->first()->name;
  }else{
    return "";
  }
}

function getPartnerName($code){
  $sap = new Company(false);
  $cardname = $sap->query("SELECT T0.[CardName] FROM OCRD T0 WHERE T0.[CardCode] = '$code'");
  if(!empty($cardname)){
    return $cardname[0]['CardName'];
  }else{
    return '';
  }
}

function getNameRequester($id)
{
 

  try {
    $sap = new Company(false);  
    $fullNameRaw = DB::raw("(ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')) as name");
    return $sap->getDb()->table('OHEM')
    ->where('empID',$id)
    ->get([$fullNameRaw])->first()->name;
  } catch (\Throwable $th) {
    return null;
  }
}

function getStockLoanCode($id){
 $stockLoan =  App\Modules\Inventory\Models\StockLoan\StockLoan::find($id)->first();
 return $stockLoan->code;

}
function getPurchaseCode($id){
  $PO = App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder::find($id);
  if($PO){
    return $PO->code;
  }
  return '';
}
function getQuotationCode($id){
 $PQ =  App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation::find($id);
 if($PQ)
 return $PQ->code;
return '';
}

function getAllQuotationsCode($id){
  $PQ = App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation::where('idRequest', $id)->where('status', '!=', '2')->select('id', 'code')->get();
  
  $array = [];
  if($PQ){
    foreach($PQ as $key => $value){
      array_push($array, ['code'=>$value->code, 'id'=>$value->id]);
    }
  }
  return $array;
}

function getSolicitanteRequest($id){
 $PR =  App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest::find($id);
 if($PR)
 return $PR->solicitante;
return '';
}
function getCodeRequest($id){
 $PR =  App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest::find($id);
 if($PR)
 return $PR->code;
return '';
}

function getIdRequest($code){
  $PR =  App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest::where('code', $code)->first();
  if($PR)
    return $PR->id;
  return '';
}

function getAllOrdersCode($id){
  $PR = App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest::find($id);
  $PQ = App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation::where('idRequest', $PR->id)->where('code_order', '!=', null)->get();

  $array = [];
  if(count($PQ)){
    foreach($PQ as $key => $value){
      
      $PO = App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder::where('status', '!=', '2')->where('idQuotation', $value->id)->get();

      foreach($PO as $index => $val){
        array_push($array, $val->code);
      }
    }
  }else{
    $PO = App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder::where('idRequest', $PR->id)->get();
    foreach($PO as $index => $val){
      array_push($array, $val->code);
    }
  }
  return $array;
}

function getAccFromWhs($whsCode){
  $sap = new Company(false);
  $query = $sap->getDb()->table('OWHS')->where('WhsCode', '=', $whsCode)->first();
  return $query;
}

function somarData($data, $dias, $meses = 0, $ano = 0){
  //passe a data no formato yyyy-mm-dd
  $data = explode("-", $data);
  $newData = date("d/m/Y", mktime(0, 0, 0, $data[1] + $meses, $data[2] + $dias, $data[0] + $ano) );
  $data = explode("/", $newData);
  $newData = $data[2].'-'.$data[1].'-'.$data[0];
  return $newData;
}

function getItemAvgPrice($code){
  $sap = new Company(false);
  $query = $sap->getValidItemQueryBuilder('OITM');
  $query->where('OITM.ItemCode','=',$code);
  
  return $query->get()->first()->LastPurPrc;
}

function getItemSAP($itemCode){

  $sap = new Company(false);
  $query = $sap->query("select TOP 1
                          OITM.ItemCode,
                          OITM.InvntryUom,
                          OITM.BuyUnitMsr,
                          OITM.DfltWH, 
                          OITM.ItemName, 
                          OITM.UserText, 
                          OITM.AvgPrice,
                          OITM.LstEvlPric,
                          OITM.LastPurPrc, 
                          B.ONHAND from OITM
                          join OITW B on b.ItemCode =  OITM.itemcode
                          where OITM.ItemCode = '{$itemCode}'");

  if(array_key_exists(0, $query)){
    return $query[0];
  }else{
    return '';
  }

}

function getLastTwelveMonths(){
  $data = array();
  for ($i = 11; $i >= 0; $i--) {
      $month = \Carbon\Carbon::today()->startOfMonth()->subMonth($i)->month;
      $year = \Carbon\Carbon::today()->startOfMonth()->subMonth($i)->year;
      array_push($data, array(
          'month' => $month,
          'year' => $year
      ));
  }
  return $data;
}

function getProviderQuotation($id){
  $sap = new Company(false);
  $cardCode = DB::table('purchase_quotation')->select('provider1')->where('id', $id)->first();

  if(is_null($cardCode->provider1)){
    return '';
  }

  $data = $sap->query("SELECT T0.[CardCode], T0.[CardName] FROM OCRD T0 WHERE T0.[CardCode] = '$cardCode->provider1'");

  if(!empty($data)){
    return $data[0];
  }else{ 
    return '';
  }
}
function getProviderName($code){
  $sap = new Company(false);

  $query = "SELECT T0.[CardCode], T0.[CardName] FROM OCRD T0 WHERE T0.[CardCode] = '$code'";
  $data = $sap->query($query);

  if(!empty($data)){
    return $data[0]['CardName'];
  }else{ 
    return '';
  }
}

function getProviderData($code){
  $sap = new Company(false);

  $query = "select top 1 OCRD.CardCode, CardName, CardFName, CardType,
            GroupCode, GroupNum, PymCode, Phone1, E_Mail, Free_Text, TaxId0 ,CNAEId,
            TaxId1, TaxId2, TaxId3, TaxId4,DebPayAcct,DpmClear, BankCode
            from OCRD 
            left join CRD7 on CRD7.CardCode = OCRD.CardCode where OCRD.CardCode = '$code'";
  $data = $sap->query($query);
  if(!empty($data)){
    return $data[0];
  }else{ 
    return '';
  }
}

function existsInSAP($table,$field, $code){
  $sap = new Company(false);
  $check = $sap->getDb()->table($table)->where($field, $code)->get();
  if(count($check) > 0) {
      $aux = true;
  }else{
      $aux = false;
  }
  return $aux;

}

function getBestPriceQuotationItem($id){

    $comparative_items = App\Modules\Purchase\Models\PurchaseQuotation\Item::where('idItemPurchaseRequest', $id)
                      ->select('purchase_quotation.code', 'purchase_quotation_items.idPurchaseQuotation', 'purchase_quotation_items.itemCode',
                               'purchase_quotation_items.itemName', 'purchase_quotation_items.qtdP1',
                               'purchase_quotation_items.priceP1', 'purchase_quotation_items.totalP1', 'OCRD.CardCode', 'OCRD.CardName',
                               'OCTG.PymntGroup')
                      ->join('purchase_quotation', 'purchase_quotation.id', '=', 'purchase_quotation_items.idPurchaseQuotation')
                      ->leftJoin('SAPHOMOLOGACAO.dbo.OCRD', 'OCRD.CardCode', '=', 'purchase_quotation.provider1')
                      ->leftJoin('SAPHOMOLOGACAO.dbo.OCTG', 'OCTG.GroupNum', '=', 'purchase_quotation.paymentTerms')
                      ->get();
                      
  $best_price = ['qtd'=>0.00,'price'=>999999999.00, 'index'=>0]; // se mudar da bug
  if(!$comparative_items->isEmpty()){
    foreach($comparative_items as $index => $item){
      $price = (Double)$item->qtdP1 * (Double)$item->priceP1;
  
      if(!empty($item->qtdP1) && $item->qtdP1 >= $best_price['qtd'] && $price < $best_price['price']){
        $best_price['price'] = (Double)$price;
        $best_price['index'] = $index;
        $best_price['qtd'] = (Double)$item->qtdP1;
      }
    }
  }

  return $comparative_items[$best_price['index']] ?? null;
  
}

function getLastPurchaseItem($itemCode){
  $sap = new Company(false);
  $query = $sap->query("SELECT TOP 1 T0.[CardCode], T2.[CardName], T1.[Price] FROM OPCH T0  
        INNER JOIN PCH1 T1 ON T0.DocEntry = T1.DocEntry
        INNER JOIN OCRD T2 ON T0.CardCode = T2.CardCode
        WHERE  
        T1.[ItemCode] = '{$itemCode}' and 
        T0.DocEntry NOT IN (SELECT T4.BaseEntry FROM ORPC T3 INNER JOIN RPC1 T4 ON T3.DocEntry = T4.DocEntry
        WHERE T4.BaseEntry IS NOT NULL and  T3.SeqCode = 1) ORDER BY T0.[DocDate] desc
        ");
  return $query[0] ?? [];
}

function getPurchaseOrdersAssociatedQuotation($id){
  $comparative_items = App\Modules\Purchase\Models\PurchaseOrder\Item::where('idItemPurchaseRequest', $id)->get();
  $purchase_orders = []; 
  
  foreach($comparative_items as $index => $item){
    array_push($purchase_orders, ['id' => $item->idPurchaseOrders, 'code' => getPurchaseCode($item->idPurchaseOrders)]);
  }
  
  return $purchase_orders;
}


function differenceBetweenTwoDatesOutputDays($initDate, $endDate){
  $carbon = new \Carbon\Carbon;
  $difference = $carbon::parse($endDate)->diffInDays($initDate);
  return $carbon::parse($initDate)->gt($carbon::parse($endDate)) ? -$difference : $difference;
}