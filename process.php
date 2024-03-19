<?php
require_once 'vendor/autoload.php';
use DiDom\Document;
use GuzzleHttp\Client;


// Получение данных из формы
$tradeNumber = $_POST['tradeNumber'];
$lotNumber = $_POST['lotNumber'];

$url = "https://nistp.ru/?trade_number=" . $tradeNumber . "&lot_number=" . $lotNumber;

$client = $client = new Client([
    'verify' => false, 
]);
$resp = $client->get($url);
$html = $resp->getBody()->getContents();
// print_r($html);
$document = new Document();
$document->loadHtml($html);
$table = $document->find('.data');
echo '<pre>';
print_r($table);
echo '</pre>';
die;

// Формирование URL для поиска


// // // Загрузка страницы поиска
// $context = stream_context_create([
//     'ssl' => [
//         'verify_peer' => false,
//         'verify_peer_name' => false,
//     ]
// ]);

// Инициализируем сеанс cURL

// Устанавливаем URL для запроса
// $url = "https://nistp.ru/?trade_number=" . $tradeNumber . "&lot_number=" . $lotNumber;

// $header = array(
//     "cache-control: max-age=0",
//     "upgrade-insecure-requests: 1",
//     "user-agent: Safari/537.36",
//     "sec-fetch-user: ?1",
//     "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
//     "signed-exchange;v=b3",
//     "x-compress: null",
//     "sec-fetch-site: none",
//     "sec-fetch-mode: navigate",
//     "accept-encoding: deflate, br",
//     "accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
// );

// $ch = curl_init($url);
// curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . "/cookie.txt");
// curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . "/cookie.txt");
// curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_HEADER, true);

// $html = curl_exec($ch);

// curl_close($ch);


// Выводим полученные данные
// var_dump($html);


// Проверка наличия лота
if (strpos($html, 'Ничего не найдено') !== false) {
    echo "Лот не найден";
} else {
    // Получение информации о лоте с указанного URL

    // Парсинг HTML для получения необходимых данных
    $dom = new DOMDocument();
    $dom->loadHTML($html);

    echo $dom->saveHTML();
die;

    // Получение URL адреса лота
    $urlAddress = $dom->getElementById('lot-url')->textContent;

    // Получение описания лота
    $description = $dom->getElementById('lot-description')->textContent;

    // Получение начальной цены лота
    $initialPrice = $dom->getElementById('initial-price')->textContent;

    // Получение email контактного лица
    $email = $dom->getElementById('contact-email')->textContent;

    // Получение телефона контактного лица
    $phone = $dom->getElementById('contact-phone')->textContent;

    // Получение ИНН должника
    $inn = $dom->getElementById('debtor-inn')->textContent;

    // Получение номера дела о банкротстве
    $caseNumber = $dom->getElementById('case-number')->textContent;

    // Получение даты торгов (начала/проведения)
    $auctionDate = $dom->getElementById('auction-date')->textContent;

    // Сохранение информации в базу данных

    // Вывод информации о лоте
    echo "URL адрес: " . $urlAddress . "<br>";
    echo "Cведения об имуществе: " . $description . "<br>";
    echo "Начальная цена лота: " . $initialPrice . "<br>";
    echo "Email контактного лица: " . $email . "<br>";
    echo "Телефон контактного лица: " . $phone . "<br>";
    echo "ИНН должника: " . $inn . "<br>";
    echo "Номер дела о банкротстве: " . $caseNumber . "<br>";
    echo "Дата торгов: " . $auctionDate . "<br>";
}