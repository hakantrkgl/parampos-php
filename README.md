ParamPOS ile Ödeme Entegrasyonu

Bu PHP betiği, 3D Secure ödeme işlemlerini işlemek için ParamPOS ödeme ağ geçidi ile nasıl entegre olunacağını gösterir.

Genel Bakış
Bu betik:

Ödeme isteği için gereken güvenlik hash değerini hesaplar.
ParamPOS test uç noktasına bir ödeme isteği gönderir.
Ödeme ağ geçidinden gelen yanıtı işler.

Gereksinimler
PHP 7.0 veya üstü
SoapClient etkinleştirilmiş olmalı
Güvenli istekler için OpenSSL etkinleştirilmiş olmalı

Dosyalar
payment.php: Ödeme işlemlerini yönetmek için ana betik.

Kurulum
PHP ortamınızın gereksinimleri karşıladığından emin olun.
Bu depoyu sunucunuza klonlayın.

payment-php.php dosyasındaki gerekli parametreleri yapılandırın.

Yapılandırma

Parametreler
$wsdlUrl: ParamPOS servisi için WSDL URL'si.
$clientCode: ParamPOS tarafından sağlanan müşteri kodu.
$clientUsername: Müşteri kullanıcı adı.
$clientPassword: Müşteri şifresi.
$guid: ParamPOS tarafından sağlanan GUID.
$gsm: Kart sahibinin GSM numarası.
$amount: İşlem tutarı.
$totalAmount: İşlem için toplam tutar.
$orderID: İşlem için benzersiz sipariş ID'si.
$transactionId: Benzersiz işlem ID'si.
$callbackUrl: İşlemden sonra kullanıcıyı yönlendirmek için URL.
$currencyCode: İşlem için para birimi kodu (ör. TRY için 949).
$ipAddress: Kullanıcının IP adresi.
$islemGuvenlikTip: İşlem güvenlik tipi (ör. '3D' 3D Secure için).
$installment: Taksit sayısı (tek çekim için 1).
$siparisAciklama: Sipariş açıklaması.

Kullanım
Adım 1: Güvenlik Hash Değerini Hesaplama
callSHA2B64 fonksiyonu, ParamPOS servisine bir istek göndererek SHA256 hash değerini hesaplar.

function callSHA2B64($wsdlUrl, $data) {
    // SOAP istemcisi kurulumu ve hash hesaplama isteği
}

Adım 2: Ödeme İsteği Oluşturma
Betik, gerekli parametreler ve hesaplanan güvenlik hash değeri ile ödeme isteğini oluşturur.

$data = [
    'G' => [
        'CLIENT_CODE' => $clientCode,
        'CLIENT_USERNAME' => $clientUsername,
        'CLIENT_PASSWORD' => $clientPassword,
    ],
    // Diğer parametreler...
];

Adım 3: Ödeme İsteğini Gönderme
Betik, ödeme isteğini ParamPOS servisine gönderir ve yanıtı işler.

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

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Last Request:\n" . htmlspecialchars($client->__getLastRequest()) . "\n";
    echo "Last Response:\n" . htmlspecialchars($client->__getLastResponse()) . "\n";
}

Adım 4: Yanıtı İşleme
Betik, ödeme ağ geçidinden gelen yanıtı kontrol eder ve işlemin başarılı olup olmadığını belirler ve hataları uygun şekilde işler.

Örnek Çıktı
Betik, hata ayıklama amacıyla ham SOAP isteğini ve yanıtını yazdırır.

Request:
...SOAP Request XML...
Response:
...SOAP Response XML...


isans
Bu proje MIT Lisansı altında lisanslanmıştır - ayrıntılar için LICENSE dosyasına bakın.

Katkıda Bulunma
Lütfen sorunları bildirin, depoyu forklayın ve pull request gönderin!

İletişim
Herhangi bir soru veya sorun için lütfen

https://www.facebook.com/khan8006
https://twitter.com/hakantrkgl
https://instagram.com/hakanturkgl
https://www.linkedin.com/in/hakanturkgl


