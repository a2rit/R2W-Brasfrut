<?php
/**
 * Created by PhpStorm.
 * User: H4
 * Date: 12/02/2018
 * Time: 07:41
 */

namespace App;


use Litiano\Sap\Company;

trait SapUtilities
{
    protected function getAccountOptions(Company $sap)
    {
        $result = $sap->getAccountsQueryBuilder()
            ->get(['AcctCode as value', \DB::raw("CONCAT(AcctCode, ' - ', AcctName) as name")]);
        return json_decode(json_encode($result), true);
    }
    protected function getAccountOptionsControle(Company $sap)
    {
        $result = $sap->getValidItemQueryBuilder('OACT')
        ->where('LocManTran', '<>', 'N')
        ->where('Postable', '=', 'Y')
        ->orderBy('AcctCode')
        ->get(['AcctCode as value', \DB::raw("CONCAT(AcctCode, ' - ', AcctName) as name")]);
        return json_decode(json_encode($result), true);
    }

    protected function getDistributionRulesOptions(Company $sap)
    {
        $result = $sap->getDistributionRulesQueryBuilder()
            ->get(['OOCR.OcrCode as value', 'OOCR.OcrName as name']);
        return json_decode(json_encode($result), true);
    }

    protected function getCostCenterOptions(Company $sap)
    {
        $result = $sap->getDb()
            ->table('OPRC')
            ->where('Active', '=', 'Y')
            ->get(['PrcCode as value', 'PrcName as name']);
        return json_decode(json_encode($result), true);
    }
    protected function getWHSOptions(Company $sap)
    {
        $result = $sap->getDb()->table('OWHS')->select('WhsCode as value', 'WhsName as name')->get();
        return json_decode(json_encode($result), true);
    }

    protected function getProjectOptions(Company $sap)
    {
        $result = $sap->getProjectsQueryBuilder()
            ->get(['PrjCode as value', 'PrjName as name']);
        return json_decode(json_encode($result), true);
    }

    protected function getModelOptions(Company $sap)
    {
        return $sap->query("select AbsEntry as value, NfmName as name from onfm");
    }

    protected function getUtilizationOptions(Company $sap)
    {
        return $sap->query("select ID as 'value', Usage as 'name' from OUSG");
    }

    protected function getSalesEmployersOptions(Company $sap)
    {
        return $sap->query("select SlpName as name, SlpCode as value from OSLP where Active = 'Y'");
    }
    protected function getTaxOptions(Company $sap)
    {
        return $sap->getDb()->table('OSTC')->select('code as value', 'name')->where('lock', '!=', 'Y')->get();
    }
    protected function getCFOPOptions(Company $sap)
    {
        return $sap->query("SELECT T0.[Code] as value, T0.[Descrip] as name FROM OCFP T0");
    }
}
