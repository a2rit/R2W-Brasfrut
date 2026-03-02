<?php

namespace App\Services;

use App\Models\Colibri\NFc;
use App\Models\Colibri\Pagamento;
use App\Models\NFCe;
use App\Models\PontoVenda;
use Illuminate\Support\Carbon;
use Throwable;
use ZipArchive;

class NfceFiles
{
    private PontoVenda $pv;

    public function __construct(PontoVenda $pv)
    {
        $this->pv = $pv;
    }

    public function scanDir(string $directory): void
    {
        $logContext = [
            'directory' => $directory,
            'pvName' => $this->pv->nome,
            'pvId' => $this->pv->id,
        ];
        if (!is_dir($directory)) {
            app('NFCeLogger')
                ->alert("O caminho fornecido em Pasta de Xmls não é um diretorio.", $logContext);
            return ;
        }

        $scanResult = scandir($directory);

        $logContext['count'] = count($scanResult);
        app('NFCeLogger')->info('Load files', $logContext);

        foreach ($scanResult as $fileName) {
            $fullPath = $directory . DIRECTORY_SEPARATOR . $fileName;
            $logContext['fullPath'] = $fullPath;
            if (!$this->loadFileOrFolder($fileName, $fullPath)) {
                continue;
            }

            if (is_dir($fullPath)) {
                $this->scanDir($fullPath);
                continue;
            }

            try {
                $fileExtension = mb_strtolower(substr($fullPath, -3));

                if ($fileExtension === 'xml') {
                    $this->processXmlFile($fileName, $directory);
                } elseif ($fileExtension === 'zip') {
                    $this->processZipFile($fileName, $directory);
                }
            } catch (Throwable $e) {
                app('NFCeLogger')->alert(
                    "Error on load file: {$e->getMessage()} - {$e->getFile()} : {$e->getLine()}",
                    $logContext
                );
            }
        }
    }

    /**
     * @throws Throwable
     */
    protected function processXmlFile(string $fileName, string $directory): void
    {
        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;
        $xml = simplexml_load_file($filePath);
        if (!$xml) {
            return;
        }

        switch ($xml->getName()) {
            case "TransactPackage": // é um arquivo do Colibri
            case "SyncPackage": // é um arquivo do Colibri
                if (isset($xml->{"fiscal.comprovante"})) {
                    $nfcColibri = new NFc();
                    $nfcColibri->loadXml($xml);
                } elseif (isset($xml->{"fiscal.comprovantemeios"}) || isset($xml->{"fiscal.comprovante_meio"})) {
                    $pagColibri = new Pagamento();
                    $pagColibri->loadXml($xml);
                }
                $this->moveFile($fileName, "Pagamentos-Processados", $directory);
                break;
            case "nfeProc": // é um NFe
                $infNFe = $xml->NFe->infNFe;
                break;
            case "NFe":
                $infNFe = $xml->infNFe;
                break;
//            case "enviNFe": // é um NFe
//                $infNFe = $xml;
//                break;
            default:
                $this->moveFile($fileName, "Arquivos-Invalidos", $directory);
                // app('NFCeLogger')->alert("Arquivo {$fileName} XML inválido");
                break;
        }

        if (!empty($infNFe)) {
            $nfcLoaded = NFCe::nfceExiste($xml['Id']) || NFCe::loadXml($infNFe, $this->pv);
            $ds = DIRECTORY_SEPARATOR;
            if ($nfcLoaded && $this->pv->id != '2') {
                $date = $this->getDateFromNfe($infNFe);
                $this->moveFile($fileName, "NFCe-Processadas{$ds}{$date}", $directory);
            }
        }
    }

    protected function processZipFile(string $fileName, string $directory)
    {
        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;
        $zip = new ZipArchive();
        if ($zip->open($filePath) === true) {
            $zip->extractTo(".");
            $zip->close();
            $this->moveFile($fileName, "Arquivos-Zip-Extraidos", $directory);
        } else {
            app('NFCeLogger')->error("Erro ao extrair Arquivo ZIP: $filePath");
        }
    }

    public function processarArquivosContingencia()
    {
        $baseDir = $this->pv->pasta_xml_contingencia . DIRECTORY_SEPARATOR . "enviados";
        if (!is_dir($baseDir) || !$this->pv->pasta_xml_contingencia) {
            app('NFCeLogger')->alert(
                "O caminho fornecido em Pasta de Xmls de contingência não é um diretorio.",
                $this->pv->only(['id', 'nome', 'pasta_xml_contingencia'])
            );
            return false;
        }

        $dir = scandir($baseDir);
        foreach ($dir as $_file) {
            try {
                $file = $baseDir . DIRECTORY_SEPARATOR . $_file;
                if (mb_strtolower(substr($file, -3)) === "xml" && is_file($file) && $xml = simplexml_load_file($file)) {
                    switch ($xml->getName()) {
                        case "nfeProc": // é um NFe
                            $nfc = new NFCe();
                            if ($nfc->loadXml($xml, $this->pv)) {
                                //$this->moverArquivo($_file, "NFCe-Processadas", $baseDir);
                            }
                            break;
                        case "enviNFe": // é um NFe
                            $nfc = new NFCe();
                            if ($nfc->loadXml($xml, $this->pv)) {
                                //$this->moverArquivo($_file, "NFCe-Processadas", $baseDir);
                            }
                            break;
                        default:
                            $this->moveFile($_file, "Arquivos-Invalidos", $baseDir);
                            app('NFCeLogger')->alert("Arquivo XML inválido");
                            break;
                    }
                }
            } catch (Throwable $e) {
                app('NFCeLogger')->alert($e->getMessage() . " - " . $e->getFile() . " : " . $e->getLine());
                app('NFCeLogger')->error($e);
            }
        }
        return true;
    }

    protected function loadFileOrFolder(string $fileName, string $fullPath): bool
    {
        $matchDate = (bool)preg_match('/^\d{4}_\d{2}_\d{2}$/', $fileName);
        if ($matchDate) {
            $date = Carbon::createFromFormat('Y_m_d', $fileName);
            if ($date->diffInDays() <= 7) {
                return true;
            }
        }

        $fileExtension = mb_strtolower(substr($fileName, -3));

        return is_file($fullPath) && in_array($fileExtension, ['zip', 'xml']);
    }

    protected function moveFile(string $fileName, string $toFolder, string $currentDir)
    {
        return;
        if (!is_writable($currentDir)) {
            return;
        }
        $ds = DIRECTORY_SEPARATOR;
        if (!is_dir($currentDir . $ds . $toFolder)) {
            mkdir($currentDir . $ds . $toFolder, 0777, true);
        }

        if (!is_writable($currentDir . $ds . $toFolder)) {
            return;
        }
        $currentFilePath = $currentDir . $ds . $fileName;
        $newFilePath = $currentDir . $ds . $toFolder . $ds . $fileName;
        copy($currentFilePath, $newFilePath);

        unlink($currentFilePath);
    }

    protected function getDateFromNfe($nfe): string
    {
        $date = Carbon::parse($nfe->ide->dhEmi->__toString());

        return $date->format('Y') . DIRECTORY_SEPARATOR . $date->format('Y-m');
    }
}