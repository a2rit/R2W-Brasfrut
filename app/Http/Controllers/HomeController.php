<?php

namespace App\Http\Controllers;

use App\logsError;
use Illuminate\Http\Request;
use Litiano\Sap\Company;
use Illuminate\Support\Facades\Response;
use App\Upload;
use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *  BalInvntAc
     * 
     * and OITM.DfltWH = b.whscode
     * 
     * case whs = 01
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function requireTable($id){
        
            $sap = new Company(false);
            $query = $sap->query("select TOP 1
            OITM.ItemCode,
            OITM.InvntryUom,
            OITM.BuyUnitMsr,
            OITM.NumInBuy,
            OITM.DfltWH, 
            OITM.ItemName, 
            OITM.UserText, 
            OITM.AvgPrice,
            OITM.LstEvlPric,
            OITM.LastPurPrc, 
            B.ONHAND
            FROM OITM
            join OITW B on b.ItemCode =  OITM.itemcode
            LEFT JOIN OITB C ON C.ItmsGrpCod = OITM.ItmsGrpCod
            where OITM.ItemCode = '{$id}'");
                                
            return Response::json($query);
    }

    public function getProductsFromWhs($id)
    {
        $pieces = explode("|", $id);
        $itemCode = $pieces[0];
        $whsCode = $pieces[1];

        $sap = new Company(false);
        $query = $sap->getDb()->table("OITW")
            ->join("OWHS", "OITW.WhsCode", "=", "OWHS.WhsCode")
            ->where("OITW.WhsCode", "=", "{$whsCode}")
            ->where("OITW.ItemCode", "=", "{$itemCode}");

        $columnsToSelect = ['OITW.WhsCode', 'OITW.OnHand', 'OWHS.WhsName'];
                                
        return Response::json($query->get($columnsToSelect));

    }
    
    public function getLastProvider($id){
        
            $sap = new Company(false);
            $query = $sap->query("SELECT TOP 1  T0.[CardCode], T0.[CardName], T1.[Price] FROM OPCH T0  
            INNER JOIN PCH1 T1 ON T0.DocEntry = T1.DocEntry 
            WHERE  
            T1.[ItemCode] = '{$id}' and 
            T0.DocEntry NOT IN (SELECT T4.BaseEntry FROM ORPC T3 INNER JOIN RPC1 T4 ON T3.DocEntry = T4.DocEntry
            WHERE T4.BaseEntry IS NOT NULL and  T3.SeqCode = 1) ORDER BY T0.[DocDate] desc
            ");
                                
            return Response::json($query);
    }

    public function getDueDate($date, $code)
    {
        try {
            $cDate = Config_date::where('codSAP', '=', $code)->get(['amount'])[0];
            $result = somarData($date, $cDate->amount);
            return response()->json($result);
        } catch (\Throwable $th) {
            return response()->json('');
        }
    }

    
    public function requireExpenses($id)
    {
        $sap = new Company(false);
        $DocNum = Session::get('idSAP');
        #$query = $sap->query("");
        return response()->json($DocNum);
    }


    public function getTableReleases($id)
    {    //lista de Lançamentos para o pn em compras a  pagar
        $sap = new Company(false);
        $query = $sap->query("SELECT DISTINCT 'NE' AS [TIPO],T0.DocNum AS [NDOC], T0.DocDate AS [EMISSAO], T1.DueDate AS [DTVENCTO], T0.CardName AS [FORNECEDOR], T0.Serial AS [NTITULO], (CAST(T1.InstlmntID AS CHAR(2)) + 'de '+ CAST(T0.Installmnt AS CHAR(2))) AS [NPARCELA], case when t0.DocCur = 'R$' then (T1.InsTotal - T1.PaidToDate) else (T1.InsTotalFC - (T1.PaidToDate/(SELECT DISTINCT W.RATE FROM ORTT W WHERE W.RATEDATE = T0.DOCDATE and W.Currency = 'EUR'))) end AS [VALOR],           T0.Comments AS [OBSERVACOES], t0.CardCode, T0.DOCENTRY AS [RI] FROM OPCH T0   JOIN PCH6 T1 ON T0.DocEntry = T1.DocEntry
                        JOIN OCRD T4 ON T0.CardCode = T4.CardCode join pch1 t2 on T0.DocEntry = T2.DocEntry WHERE T1.STATUS =  'O' and T0.CardCode = '{$id}'
                        group by T0.DocNum , T0.DocDate ,T1.DueDate,T0.CardName,T0.Serial ,T1.InstlmntID,T0.Installmnt,T0.DocCur,T1.InsTotal,T1.PaidToDate,T1.InsTotalFC, T0.Comments, t0.CardCode, T0.DOCENTRy UNION SELECT DISTINCT 'AT' AS [TIPO],T0.DocNum AS [NDOC], T0.DocDate AS [EMISSAO], T1.DueDate AS [DTVENCTO],T0.CardName AS [FORNECEDOR],T0.Serial AS [NTITULO],(CAST(T1.InstlmntID AS CHAR(2)) +  'de ' + CAST(T0.Installmnt AS CHAR(2))) AS [NPARCELA],(T1.InsTotal - T1.PaidToDate) AS [VALOR],T0.Comments AS [OBSERVACOES], t0.CardCode, T0.DOCENTRY FROM ODPO T0 JOIN DPO6 T1 ON T0.DocEntry = T1.DocEntry JOIN OCRD T4 ON T0.CardCode = T4.CardCode join dpo1 t2 on T0.DocEntry = T2.DocEntry WHERE T1.STATUS =  'O' and T0.CardCode = '{$id}' group by T0.DocNum , T0.DocDate ,T1.DueDate,T0.CardName,T0.Serial ,T1.InstlmntID,T0.Installmnt,T0.DocCur,T1.InsTotal,T1.PaidToDate,T1.InsTotalFC , T0.Comments, t0.CardCode, T0.DOCENTRy
                        UNION SELECT DISTINCT'LC' AS [TIPO], T0.TransId AS [NDOC], T0.RefDate AS [EMISSAO], T0.DueDate AS [DTVENCTO],T4.CardName AS [FORNECEDOR], T0.TransId AS [NTITULO],'' AS [NPARCELA], CASE WHEN T1.BalDueCred = 0 THEN T1.BALDUEDEB*-1 ELSE T1.BalDueCred END  AS [VALOR], T0.Memo AS [OBSERVACOES], t4.CardCode, T0.TransId
                        FROM OJDT T0 JOIN JDT1 T1 ON T0.TransId = T1.TransId JOIN OCRD T4 ON T1.ShortName = T4.CardCode WHERE (T0.TransId NOT IN (SELECT T5.DocTransId FROM VPM2 T5) OR T0.TransId NOT IN (SELECT T6.TransId FROM OVPM T6)) AND T4.CardType = 'S'
                        AND (T1.BalDueCred+T1.BALDUEDEB) <> 0 AND T0.TransType = 30 UNION ALL SELECT DISTINCT 'DEV-NE' AS [TIPO],T0.DocNum AS [NDOC], T0.DocDate AS [EMISSAO], T1.DueDate AS [DTVENCTO], T0.CardName AS [FORNECEDOR],T0.Serial AS [NTITULO], (CAST(T1.InstlmntID AS CHAR(2)) + 'de '+ CAST(T0.Installmnt AS CHAR(2))) AS [NPARCELA], case when t0.DocCur = 'R$' then (T1.InsTotal - T1.PaidToDate)*-1 else (T1.InsTotalFC - (T1.PaidToDate/(SELECT DISTINCT W.RATE FROM ORTT W WHERE W.RATEDATE = T0.DOCDATE and W.Currency = 'EUR')))*-1 end AS [VALOR], T0.Comments AS [OBSERVACOES], t0.CardCode, T0.DOCENTRY AS [RI] FROM ORPC T0 JOIN RPC6 T1 ON T0.DocEntry = T1.DocEntry JOIN OCRD T4 ON T0.CardCode = T4.CardCode join RPC1 t2 on T0.DocEntry = T2.DocEntry WHERE T1.STATUS =  'O' and T0.CardCode = '{$id}' group by T0.DocNum , T0.DocDate ,T1.DueDate,T0.CardName,T0.Serial ,T1.InstlmntID,T0.Installmnt,T0.DocCur,T1.InsTotal,T1.PaidToDate,T1.InsTotalFC ,T0.Comments, t0.CardCode, T0.DOCENTRy");
        return response()->json($query);
    }

    //pega o status da pedido de compra para  setar na tabela em search
    public function getStatusPO($id)
    {
        $sap = new Company(false);
        $query = $sap->query("SELECT T1.DocNum, T1.DocStatus, T1.CANCELED FROM OCRD T0
                            INNER JOIN OPOR T1 ON T0.CardCode = T1.CardCode
                            INNER JOIN CRD7 T2 ON T0.CardCode = T2.CardCode
                            WHERE T1.DocNum = '{$id}'AND T2.Address=''
                            AND T2.TaxId0 is not null ");

        if (($query[0]['DocStatus'] == 'O') && ($query[0]['CANCELED'] == 'N')) {
            $query = 'Aberto';
        } else if (($query[0]['DocStatus'] == 'C') && ($query[0]['CANCELED'] == 'N')) {
            $query = 'Fechado';
        } else if (($query[0]['DocStatus'] == 'C') && ($query[0]['CANCELED'] == 'Y')) {
            $query = 'Cancelado';
        } else if (($query[0]['DocStatus'] == 'O') && ($query[0]['CANCELED'] == 'Y')) {
            $query = 'Cancelado';
        }

        return $query;
    }

    public function requireTablePNF($id)
    {
        $sap = new Company(false);
        $id = '%' . $id . '%';
        $query = $sap->query("SELECT T0.CardCode,T0.GroupCode, T0.CardName, T1.TaxId4, T1.TaxId0 FROM OCRD  T0
                                  INNER JOIN CRD7 T1 ON T0.CardCode = T1.CardCode
                                  WHERE t1.Address=''
                                  AND T1.TaxId4 is not null AND T0.GroupCode = '100' AND (T0.CardCode collate SQL_Latin1_General_CP1_CI_AI like '{$id}'
                  								or T0.CardName collate SQL_Latin1_General_CP1_CI_AI like '{$id}'
                  								or T1.TaxId4 collate SQL_Latin1_General_CP1_CI_AI like '{$id}'
                  								or T1.TaxId0 collate SQL_Latin1_General_CP1_CI_AI like '{$id}')");

        return response()->json($query);
    }

    public function requireTablePN($id)
    {
        $sap = new Company(false);
        $id = '%' . $id . '%';
        $query = $sap->query("SELECT T0.CardCode,T0.GroupCode, T0.CardName, T1.TaxId4, T1.TaxId0 FROM OCRD  T0
                                  INNER JOIN CRD7 T1 ON T0.CardCode = T1.CardCode
                                  WHERE T0.CardType != 'S' AND t1.Address=''
                                  AND T1.TaxId4 is not null AND (T0.CardCode collate SQL_Latin1_General_CP1_CI_AI like '{$id}'
                  								or T0.CardName collate SQL_Latin1_General_CP1_CI_AI like '{$id}'
                  								or T1.TaxId4 collate SQL_Latin1_General_CP1_CI_AI like '{$id}'
                  								or T1.TaxId0 collate SQL_Latin1_General_CP1_CI_AI like '{$id}')");

        return response()->json($query);
    }

    public function requireTablePNJ($id)
    {
        $sap = new Company(false);
        $id = '%' . $id . '%';
        $query = $sap->query("SELECT T0.CardCode,T0.GroupCode, T0.CardName, COALESCE(T0.CardFName, ' ') AS CardFName, T1.TaxId4, T1.TaxId0 FROM OCRD  T0
                                  INNER JOIN CRD7 T1 ON T0.CardCode = T1.CardCode
                                  WHERE T0.CardType = 'S' AND t1.Address=''
                                  AND T1.TaxId4 is not null AND (T0.CardCode collate SQL_Latin1_General_CP1_CI_AI like '{$id}'
                                                or T0.CardFName collate SQL_Latin1_General_CP1_CI_AI like '{$id}'
                  								or T0.CardName collate SQL_Latin1_General_CP1_CI_AI like '{$id}'
                  								or T1.TaxId4 collate SQL_Latin1_General_CP1_CI_AI like '{$id}'
                  								or T1.TaxId0 collate SQL_Latin1_General_CP1_CI_AI like '{$id}')");

        return response()->json($query);
    }

    public function requirePN($id)
    {
        $sap = new Company(false);
        $query = $sap->query("SELECT distinct T0.CardCode,T0.GroupCode,T0.PymCode, T0.GroupNum, T0.CardName,T1.AdresType, T2.TaxId4,T2.TaxId0,T1.Street, T1.StreetNo, T1.Block, T1.City, T1.State FROM OCRD  T0
                                        left JOIN CRD7 T2 ON T0.CardCode = T2.CardCode
                                        left JOIN CRD1 T1 ON T0.CardCode = T1.CardCode
                                        WHERE T0.CardCode = '{$id}'");
        return response()->json($query);
    }

    public function getTablesUsers($search)
    {
        $search = '%' . $search . '%';
        $query = DB::select("SELECT T1.id, T1.name, T1.email FROM users T1
                                WHERE T1.id like '{$search}'
                                or T1.name collate SQL_Latin1_General_CP1_CI_AI like '{$search}'
                                or T1.email collate SQL_Latin1_General_CP1_CI_AI like '{$search}'");
        return response()->json($query);
    }

    public function dataTablesAnyDataAbstract(Request $request)
    {
        $query = Model::query();
        $recordsTotal = $query->count();
        $query->offset($request->get("start"));
        $query->limit($request->get("length"));
        $columns = $request->get("columns");
        $columnsToSelect = ['*'];

        $search = $request->get('search');
        // @TODO Seacrh with where

        $order = $request->get('order');
        $query->orderBy($columns[$order[0]['column']]['name'], $order[0]['dir']);

        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $query->count(),
            "data" => $query->get($columnsToSelect)
        ]);
    }

    public function searchItem($code)
    {
        $sap = new Company(false);
        $query = $sap->query("SELECT DISTINCT T0.Number,T0.RefDate, T0.DueDate, T0.TaxDate,  T0.Memo FROM OJDT T0
                            INNER JOIN JDT1 T1 ON T0.TransId = T1.TransId INNER JOIN OACT T2 ON T1.Account = T2.AcctCode
                            left JOIN OOCR T3 ON T1.ProfitCode = T3.OcrCode
                            left JOIN OPRJ T4 ON T0.Project = T4.PrjCode WHERE T0.Number = '{$code}'");
        return response()->json($query);
    }

    public function getCurrencyQuote()
    {
        try {
            $soap = new \SoapClient("https://www3.bcb.gov.br/sgspub/JSP/sgsgeral/FachadaWSSGS.wsdl");
            $valor = $soap->getValor(21619, DATE('d/m/Y'));
            return response()->json(number_format($valor, 2, ',', '.'));
        } catch (\SoapFault $fault) {
            return response()->json('0,00');

        }

    }

    public function getCashFlow($type = 3)
    {
        try {
            switch ($type) {
                case '0':
                    $query = DB::SELECT("SELECT T0.id, T0.description as value FROM cash_flows as T0 WHERE T0.module = 'C' and T0.status = '1'");
                    break;
                case '1':
                    $query = DB::SELECT("SELECT T0.id, T0.description as value FROM cash_flows as T0 WHERE T0.module = 'V' and T0.status = '1'");
                    break;
            }
            return response()->json($query);

        } catch (\Exception $fault) {
            return response()->json($fault);
        }


    }

    public function searchTableItem($code)
    {
        $sap = new Company(false);
        $query = $sap->query("SELECT T2.AcctCode, T2.AcctName, T1.Debit, T1.Credit, T3.OcrName, T4.PrjName FROM OJDT T0
                            INNER JOIN JDT1 T1 ON T0.TransId = T1.TransId INNER JOIN OACT T2 ON T1.Account = T2.AcctCode
                            left JOIN OOCR T3 ON T1.ProfitCode = T3.OcrCode
                            left JOIN OPRJ T4 ON T0.Project = T4.PrjCode WHERE T0.Number = '{$code}'");
        return response()->json($query);
    }

    public function gerarAcessoRapido()
    {
        try {
            $user = Auth::user();
    
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => config('tomticket.url')."criar_acesso_cliente/".config('tomticket.token'),
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => [
                    'tipo_identificacao' => '1',
                    'identificador' => $user->email
                ]
                ]);
                // Envio e armazenamento da resposta
                $response = json_decode(curl_exec($curl));
                
                // Fecha e limpa recursos
            curl_close($curl);

            if(!empty($response)){
                if(isset($response->erro)){
                    return redirect()->route('initial')->withErrors("Erro ao tentar acessar a plataforma TomTicket: {$response->errorcode} - {$response->mensagem}");
                }
                if(!empty($response->url)){
                    return redirect($response->url);
                }
            }
        } catch (\Exception $e) {
            return redirect()->route('initial')->withErrors("{$e->getMessage()}");
        }
    }

    public function registerFrontendError(Request $request){
        if(!empty($request->message) && !empty($request->filename)){
            $logsError = new logsError();
            $logsError->saveInDB('FNDE', $request->filename, $request->message);
        }
    }
}
