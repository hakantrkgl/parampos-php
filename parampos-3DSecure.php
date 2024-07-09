<?php

function calculateHash($clientCode, $guid, $installment, $amount, $totalAmount, $orderID, $errorUrl, $successUrl) {
    $data = $clientCode . $guid . $installment . $amount . $totalAmount . $orderID . $errorUrl . $successUrl;
    return base64_encode(hash('sha256', $data, true));
}

function callSHA2B64($wsdlUrl, $data) {
    try {
        $client = new SoapClient($wsdlUrl, [
            'trace' => 1,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ])
        ]);

        $params = ['Data' => $data];
        $response = $client->SHA2B64($params);

        return $response->SHA2B64Result;

    } catch (Exception $e) {
        echo "Error in SHA2B64: " . $e->getMessage() . "\n";
        echo "Last Request:\n" . htmlspecialchars($client->__getLastRequest()) . "\n";
        echo "Last Response:\n" . htmlspecialchars($client->__getLastResponse()) . "\n";
        return false;
    }
}

$wsdlUrl = 'https://test-dmz.param.com.tr/turkpos.ws/service_turkpos_test.asmx?wsdl';
$clientCode = '10738';
$clientUsername = 'Test';
$clientPassword = 'Test';
$guid = '0c13d406-873b-403b-9c09-a5766840d98c';
$gsm = '5061234567';
$amount = '1007,55';  // Tutarı virgül ile ayırın
$totalAmount = '1007,55'; // Toplam tutarı işlem tutarı ile aynı yapıyoruz
$orderID = 'string11';
$transactionId = 'string11';
$callbackUrl = 'http://localhost:8000/payment/callback';
$currencyCode = '949';  // Turkish Lira için Doviz Kodu
$ipAddress = '217.131.106.179'; // Örnek IP adresi
$islemGuvenlikTip = '3D'; // Güvenlik tipi
$installment = '1'; // Tek çekim
$siparisAciklama = 'Test Siparişi';

// İşlem güvenlik stringi oluşturma
$islemGuvenlikStr = $clientCode . $guid . $installment . $amount . $totalAmount . $orderID . $callbackUrl . $callbackUrl;

// SHA2B64 metodunu çağırarak hash hesaplama
$islemHash = callSHA2B64($wsdlUrl, $islemGuvenlikStr);

if ($islemHash === false) {
    die("SHA2B64 hash calculation failed.");
}

$data = [
    'G' => [
        'CLIENT_CODE' => $clientCode,
        'CLIENT_USERNAME' => $clientUsername,
        'CLIENT_PASSWORD' => $clientPassword,
    ],
    'GUID' => $guid,
    'KK_Sahibi' => 'John Doe',
    'KK_No' => '4155650100416111',
    'KK_SK_Ay' => '01',
    'KK_SK_Yil' => '50',
    'KK_CVC' => '715',
    'KK_Sahibi_GSM' => $gsm,
    'Hata_URL' => $callbackUrl,
    'Basarili_URL' => $callbackUrl,
    'Siparis_ID' => $orderID,
    'Siparis_Aciklama' => $siparisAciklama,
    'Taksit' => $installment,
    'Islem_Tutar' => $amount,
    'Toplam_Tutar' => $totalAmount,
    'Islem_Hash' => $islemHash,
    'Islem_Guvenlik_Tip' => $islemGuvenlikTip,
    'Islem_ID' => $transactionId,
    'IPAdr' => $ipAddress,
    'Ref_URL' => $callbackUrl,
    'Doviz_Kodu' => $currencyCode,
    'Data1' => '',
    'Data2' => '',
    'Data3' => '',
    'Data4' => '',
    'Data5' => '',
    'Data6' => '',
    'Data7' => '',
    'Data8' => '',
    'Data9' => '',
    'Data10' => ''
];

try {
    $client = new SoapClient($wsdlUrl, [
        'trace' => 1,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE,
        'stream_context' => stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ])
    ]);

    $response = $client->Pos_Odeme($data);

    echo "Request:\n" . htmlspecialchars($client->__getLastRequest()) . "\n";
    echo "Response:\n" . htmlspecialchars($client->__getLastResponse()) . "\n";

    $responseObj = $response->Pos_OdemeResult;
    if ($responseObj->Sonuc > 0 && !empty($responseObj->UCD_URL)) {
        // İşlem başarılı, yönlendirme URL'si var
        header("Location: " . $responseObj->UCD_URL);
        exit;
    } else {
        // İşlem başarısız
        echo "İşlem Başarısız: " . $responseObj->Sonuc_Str;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Last Request:\n" . htmlspecialchars($client->__getLastRequest()) . "\n";
    echo "Last Response:\n" . htmlspecialchars($client->__getLastResponse()) . "\n";
}

?>
