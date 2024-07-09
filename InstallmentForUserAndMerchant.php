<?php

class Client
{
    protected $clientCode;
    protected $clientUsername;
    protected $clientPassword;
    protected $guid;
    protected $mode;
    protected $serviceUrl;

    public function __construct($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl)
    {
        $this->clientCode = $clientCode;
        $this->clientUsername = $clientUsername;
        $this->clientPassword = $clientPassword;
        $this->guid = $guid;
        $this->mode = $mode;
        $this->serviceUrl = $serviceUrl;
    }
}

class InstallmentForMerchant extends Client
{
    private $response;

    public function send()
    {
        $options = [
            'soap_version' => SOAP_1_1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'trace' => 1,
            'stream_context' => stream_context_create(
                [
                    'ssl' => [
                        'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ]
            )
        ];
        $client = new \SoapClient($this->serviceUrl, $options);

        $installmentsObj = new \stdClass();
        $installmentsObj->G = new \stdClass();
        $installmentsObj->G->CLIENT_CODE = $this->clientCode;
        $installmentsObj->G->CLIENT_USERNAME = $this->clientUsername;
        $installmentsObj->G->CLIENT_PASSWORD = $this->clientPassword;
        $installmentsObj->GUID = $this->guid;

        $this->response = $client->TP_Ozel_Oran_Liste($installmentsObj);

        return $this;
    }

    public function fetchInstallment()
    {
        $results = [];
        if ($this->response->TP_Ozel_Oran_ListeResult->Sonuc < 0) {
            return [
                'Sonuc' => $this->response->TP_Ozel_Oran_ListeResult->Sonuc,
                'Sonuc_Str' => $this->response->TP_Ozel_Oran_ListeResult->Sonuc_Str,
            ];
        }

        $result = $this->response->TP_Ozel_Oran_ListeResult;
        $sonuc = $result->{'Sonuc'};
        $sonucStr = $result->{'Sonuc_Str'};
        if ($sonuc <= 0) {
            return [
                'Sonuc' => $sonuc,
                'Sonuc_Str' => $sonucStr,
            ];
        }
        $DT_Bilgi = $result->{'DT_Bilgi'};
        $xml = $DT_Bilgi->{'any'};
        $xmlStr = '<?xml version=\'1.0\' standalone=\'yes\'?><root>' . $xml . '</root>';
        $xmlStr = str_replace(array("diffgr:", "msdata:"), '', $xmlStr);
        $data = @simplexml_load_string($xmlStr);
        $list = $data->diffgram->NewDataSet;
        $installmentsArr = [];
        foreach ($list->DT_Ozel_Oranlar as $instData) {
            $installmentsArr[strtoupper($instData->Kredi_Karti_Banka)] = (array)$instData;
        }
        return $installmentsArr;
    }
}

class InstallmentForUser extends Client
{
    private $response;

    public function send()
    {
        $options = [
            'soap_version' => SOAP_1_1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'trace' => 1,
            'stream_context' => stream_context_create(
                [
                    'ssl' => [
                        'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ]
            )
        ];
        $client = new \SoapClient($this->serviceUrl, $options);

        $installmentsObj = new \stdClass();
        $installmentsObj->G = new \stdClass();
        $installmentsObj->G->CLIENT_CODE = $this->clientCode;
        $installmentsObj->G->CLIENT_USERNAME = $this->clientUsername;
        $installmentsObj->G->CLIENT_PASSWORD = $this->clientPassword;
        $installmentsObj->GUID = $this->guid;

        $this->response = $client->TP_Ozel_Oran_SK_Liste($installmentsObj);

        return $this;
    }

    public function fetchInstallment()
    {
        $results = [];
        if ($this->response->TP_Ozel_Oran_SK_ListeResult->Sonuc < 0) {
            return [
                'Sonuc' => $this->response->TP_Ozel_Oran_SK_ListeResult->Sonuc,
                'Sonuc_Str' => $this->response->TP_Ozel_Oran_SK_ListeResult->Sonuc_Str,
            ];
        }

        $result = $this->response->TP_Ozel_Oran_SK_ListeResult;
        $sonuc = $result->{'Sonuc'};
        $sonucStr = $result->{'Sonuc_Str'};
        if ($sonuc <= 0) {
            return [
                'Sonuc' => $sonuc,
                'Sonuc_Str' => $sonucStr,
            ];
        }
        $DT_Bilgi = $result->{'DT_Bilgi'};
        $xml = $DT_Bilgi->{'any'};
        $xmlStr = '<?xml version=\'1.0\' standalone=\'yes\'?><root>' . $xml . '</root>';
        $xmlStr = str_replace(array("diffgr:", "msdata:"), '', $xmlStr);
        $data = @simplexml_load_string($xmlStr);
        $list = $data->diffgram->NewDataSet;
        $installmentsArr = [];
        foreach ($list->DT_Ozel_Oranlar_SK as $instData) {
            $installmentsArr[strtoupper($instData->Kredi_Karti_Banka)] = (array)$instData;
        }
        return $installmentsArr;
    }
}

$wsdlUrl = 'https://test-dmz.param.com.tr/turkpos.ws/service_turkpos_test.asmx?wsdl';
$clientCode = '10738';
$clientUsername = 'Test';
$clientPassword = 'Test';
$guid = '0c13d406-873b-403b-9c09-a5766840d98c';
$mode = 'TEST';
$serviceUrl = $wsdlUrl;

$merchant = new InstallmentForMerchant($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl);
$merchant->send();
$merchantRates = $merchant->fetchInstallment();

$user = new InstallmentForUser($clientCode, $clientUsername, $clientPassword, $guid, $mode, $serviceUrl);
$user->send();
$userRates = $user->fetchInstallment();

echo "<h2>Firma POS Oranları</h2>";
echo "<table border='1'>
        <tr>
            <th>Kredi Kartı Banka</th>
            <th>Tek Çekim</th>
            <th>2 Taksit</th>
            <th>3 Taksit</th>
            <th>4 Taksit</th>
            <th>5 Taksit</th>
            <th>6 Taksit</th>
            <th>7 Taksit</th>
            <th>8 Taksit</th>
            <th>9 Taksit</th>
            <th>10 Taksit</th>
            <th>11 Taksit</th>
            <th>12 Taksit</th>
        </tr>";

foreach ($merchantRates as $bank => $rate) {
    echo "<tr>
            <td>{$bank}</td>
            <td>{$rate['MO_01']}</td>
            <td>{$rate['MO_02']}</td>
            <td>{$rate['MO_03']}</td>
            <td>{$rate['MO_04']}</td>
            <td>{$rate['MO_05']}</td>
            <td>{$rate['MO_06']}</td>
            <td>{$rate['MO_07']}</td>
            <td>{$rate['MO_08']}</td>
            <td>{$rate['MO_09']}</td>
            <td>{$rate['MO_10']}</td>
            <td>{$rate['MO_11']}</td>
            <td>{$rate['MO_12']}</td>
          </tr>";
}

echo "</table>";

echo "<h2>Kullanıcı POS Oranları</h2>";
echo "<table border='1'>
        <tr>
            <th>Kredi Kartı Banka</th>
            <th>Tek Çekim</th>
            <th>2 Taksit</th>
            <th>3 Taksit</th>
            <th>4 Taksit</th>
            <th>5 Taksit</th>
            <th>6 Taksit</th>
            <th>7 Taksit</th>
            <th>8 Taksit</th>
            <th>9 Taksit</th>
            <th>10 Taksit</th>
            <th>11 Taksit</th>
            <th>12 Taksit</th>
        </tr>";

foreach ($userRates as $bank => $rate) {
    echo "<tr>
            <td>{$bank}</td>
            <td>{$rate['MO_01']}</td>
            <td>{$rate['MO_02']}</td>
            <td>{$rate['MO_03']}</td>
            <td>{$rate['MO_04']}</td>
            <td>{$rate['MO_05']}</td>
            <td>{$rate['MO_06']}</td>
            <td>{$rate['MO_07']}</td>
            <td>{$rate['MO_08']}</td>
            <td>{$rate['MO_09']}</td>
            <td>{$rate['MO_10']}</td>
            <td>{$rate['MO_11']}</td>
            <td>{$rate['MO_12']}</td>
          </tr>";
}

echo "</table>";
?>
