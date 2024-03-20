<?php
require_once 'vendor/autoload.php';
use DiDom\Document;
use GuzzleHttp\Client;


// Получение данных из формы
$tradeNumber = $_POST['tradeNumber'] ?? '41114-ОАОФ';
$lotNumber = $_POST['lotNumber'] ?? '1';

$url = "https://nistp.ru/?trade_number=" . $tradeNumber . "&lot_number=" . $lotNumber;

$client = $client = new Client([
    'verify' => false, 
]);

$resp = $client->get($url);
$html = $resp->getBody()->getContents();
// print_r($html);

$document = new Document();
$document->loadHtml($html);

$table = $document->first('table.data');
$rows = $table->find('tr');
$link = $document->find('table.data tbody tr td a')[0]->getAttribute('href');
$data = [];

foreach ($rows as $row) {
    $rowData = [];
    $cells = $row->find('td');
    foreach ($cells as $cell) {
        $rowData[] = $cell->text();
    }
    $data[] = $rowData;
}

echo '<pre>';
// print_r($link);
echo '</pre>';

$resp2 = $client->get($link);
$html2 = $resp2->getBody()->getContents();
$document2 = new Document();
$document2->loadHtml($html2);
// print_r($html2);

$rows = $document2->find('.node_view tr');
$result = [];
$result['link'] = $link;


foreach ($rows as $row) {
    $cells = $row->find('td');

    if (count($cells) >= 2) {
        $label = $cells[0]->text();
        $value = $cells[1]->text();

      if ($label === 'Cведения об имуществе (предприятии) должника, выставляемом на торги, его составе, характеристиках, описание' || $label === 'Начальная цена' || $label === 'E-mail' || $label === 'Телефон' || $label === 'Номер дела о банкротстве' || $label === 'Дата проведения') {
        $result[$label] = $value;
      }
        
    }
}

$tables = $document2->find('table.node_view');

foreach ($tables as $table) {

    $headers = $table->find('th');

     $header = !empty($headers) ? $headers[0]->text() : '';

        if (trim($header) === 'Информация о должнике') {

            $rows = $table->find('tr');
          foreach ($rows as $row) {
          $label = $row->find('td.label');

          if (!empty($label)) {
              $labelText = $label[0]->text();

              if ($labelText === 'ИНН') {
                $innElements = $row->find('td');

                if (count($innElements) > 1) {
                    $inn = $innElements[1]->text();
                    $result['inn'] = $inn;;
                    break; // Прерываем цикл после того, как найдем ИНН
                }
              }
          }
        }
        }
}

echo '<pre>';
print_r($result);
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