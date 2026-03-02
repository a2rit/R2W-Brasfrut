<?php

namespace App\Models;

class SAP
{
    /**
     * @INFO
     * Realizando alguns testes, quando a classe SAP é instanciada e busca um bussines object na mesma função,
     * o CLI do PHP para de funcionar.
     * Mas se eu instanciar a classe em uma função e passa-la como parametro para outra função que busca o bussines object
     * e faz a mesma coisa, tudo funciona perfeitamente.
     * Que bruxaria é essa???
     * Coisas da DI API.
     */
    /**
     * @var $_com Mixed
     */
    protected $_com;
    /**
     * @var $_db \PDO;
     */
    public $_db;

    protected $disconnect;

    public function __construct($setDb = true, $setConnection = true, $disconnect = true)
    {
        if ($setConnection == true){
            $this->setConnection();
        }
        if ($setDb == true) {
            $this->setDb();
        }
        $this->disconnect = $disconnect;
    }

    public function __destruct()
    {
        if($this->disconnect && $this->_com){
            $this->disconnect();
        }
    }

    protected function setConnection(){
        try{
            $this->_com = new \COM("SAPbobsCOM.Company", [], 65001); // 65001 Seta como UTF-8
        }catch (\Exception $e){
            throw  new \Exception("Erro ao Conectar com SAP: ".$e->getMessage());
        }
        $this->_com->DbServerType = (int)config('sap.db.type');//env("SAP_DB_SERVER_TYPE");
        $this->_com->Server = config('sap.server');//env("SAP_SERVER_ADDRESS");
        $this->_com->CompanyDB = config('sap.db.database');//env("SAP_DB_NAME");
        $this->_com->LicenseServer = config('sap.license_server');//env("SAP_LICENSE_SERVER");
        $this->_com->UserName = config('sap.username');//env("SAP_USER");
        $this->_com->DbUserName = config('sap.db.username');//env("SAP_DB_USER");
        $this->_com->Password = config('sap.password');//env("SAP_PASSWORD");
        $this->_com->DbPassword = config('sap.db.password');//env("SAP_DB_PASSWORD");
        $this->_com->language = 29; // PT-BR
        if (config('sap.sld_server') !== null && property_exists($this->_com, "SLDServer")) {
            $this->_com->SLDServer = config("sap.sld_server");
        }
        $retVal = $this->_com->Connect();

        if($retVal != "0"){
            throw new \Exception("Não foi possivel conectar com o SAP: " . $this->_com->GetLastErrorDescription());
        }
    }

    protected function setDb(){
        $server = config('sap.server');//env("SAP_SERVER_ADDRESS");
        $port = config('sap.db.port');//env("SAP_SQL_PORT");
        $database = config('sap.db.database');//env("SAP_DB_NAME");
        $db = new \PDO("sqlsrv:Server=$server,$port;Database=$database", config('sap.db.username'), config('sap.db.password'));
        if($db == false){
            throw new \Exception("Erro ao conectar com SqlServer");
        }
        $this->_db = $db;
    }

    protected function disconnect(){
        if($this->_com){
            $this->_com->Disconnect();
        }
    }

    public function getBussinesObject($code){
        return $this->_com->GetBusinessObject($code);
    }

    public function getLastErrorDescription(){
        return $this->_com->GetLastErrorDescription();
    }

    public function getNewObjectKey(){
        return $this->_com->GetNewObjectKey();
    }

    /**
     * @return string
     * Não Funciona!!!
     */
    protected function getNewObjectCode(){
        $codigo = "";
        $this->_com->GetNewObjectCode($codigo);
        return $codigo;
    }

    public function query($query, $parametros = null){
        $stmt = $this->_db->prepare($query);
        $stmt->execute($parametros);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}