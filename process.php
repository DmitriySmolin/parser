<?php
require_once 'vendor/autoload.php';
use DiDom\Document;
use GuzzleHttp\Client;


class DataProcessor {
    private $client;
    private $data;
    public $db; // Явное объявление свойства
    public function __construct(Client $client) {
        $this->client = $client;
        $this->data = new stdClass();
      $this->db = new SQLite3('lots.sqlite'); // Подключение к базе данных SQLite
      $this->createLotsTable(); // Создание таблицы lots, если она не существует
    }

    public function processFormData($tradeNumber, $lotNumber) {
        $url = "https://nistp.ru/?trade_number=$tradeNumber&lot_number=$lotNumber";
        
        $pageTradeList = $this->getDocumentFromUrl($url);

        $baknrotHref= $this->getLinkBankrot($pageTradeList);

        // if(!$this->isLotNotFound($baknrotHref)) {
        //   return false;
        // }
        
 
        $pageTradeView = $this->getDocumentFromUrl($baknrotHref);

        $this->processContactPersontData($pageTradeView->find('.node_view tr'));
        $this->processLotData($pageTradeView->find("table#table_lot_$lotNumber tr"));
        $this->processBankrotData($pageTradeView->find('table.node_view'));
        $this->insertLot(); // Вставка данных в базу
        return $this->data;
    }

    private function isLotNotFound($href) {
    if(!$href) {
      echo "Лот не найден";
      return false;
    }
  }

  private function getLinkBankrot($document) {
      $links = $document->find('table.data tbody tr td a');
      if (!empty($links)) {
          $link = $links[0]->attr('href');
          if ($link) {
             $this->data->href = $link;
              return $link;
          }
      }
      return false;
  }

    private function getDocumentFromUrl($url) {
        $response = $this->client->get($url);
        $html = $response->getBody()->getContents();
        $document = new Document();
        $document->loadHtml($html);
        return $document;
    }

    private function processContactPersontData($rows) {
        $labelMappings = [
            'E-mail' => 'email',
            'Телефон' => 'phone',
            'Номер дела о банкротстве' => 'bankruptcy_case_number',
            'Дата проведения' => 'date_auction',
        ];

        $this->processData($rows, $labelMappings);
    }

    private function processLotData($rows) {
        $labelMappings = [
            'Cведения об имуществе (предприятии) должника, выставляемом на торги, его составе, характеристиках, описание' => 'debtor_property_information',
            'Начальная цена' => 'start_price',
        ];

        $this->processData($rows, $labelMappings);
    }

    private function processData($rows, $labelMappings) {
        foreach ($rows as $row) {
            $cells = $row->find('td');

            if (count($cells) >= 2) {
                $label = $cells[0]->text();
                $value = $cells[1]->text();

                if (isset($labelMappings[$label])) {
                    $mappedKey = $labelMappings[$label];
                    $this->data->$mappedKey = $value;
                }
            }
        }
    }

    private function processBankrotData($tables) {
        foreach ($tables as $table) {
            $headers = $table->find('th');
            $header = !empty($headers) ? $headers[0]->text() : '';
    
            if (trim($header) !== 'Информация о должнике') {
                continue;
            }
    
            $this->processDebtorITN($table->find('tr'));
            break; 
        }
    }
    
  private function processDebtorITN($rows) {
      foreach ($rows as $row) {
          $label = $row->find('td.label');

          if (!empty($label) && isset($label[0]) && $label[0]->text() === 'ИНН') {
              $itnElements = $row->find('td');

              if (!empty($itnElements) && isset($itnElements[1])) {
                  $this->data->debtor_ITN = $itnElements[1]->text();
                  return;
              }
          }
      }
  }

  private function insertLot() {
          $query = "INSERT OR REPLACE INTO lots (trade_number, email, phone, bankruptcy_case_number, debtor_property_information, start_price, debtor_ITN)
              VALUES (:trade_number, :email, :phone, :bankruptcy_case_number, :debtor_property_information, :start_price, :debtor_ITN)";

          $stmt = $this->db->prepare($query);
          $stmt->bindValue(':trade_number', $this->data->trade_number);
          $stmt->bindValue(':email', $this->data->email);
          $stmt->bindValue(':phone', $this->data->phone);
          $stmt->bindValue(':bankruptcy_case_number', $this->data->bankruptcy_case_number);
          $stmt->bindValue(':debtor_property_information', $this->data->debtor_property_information);
          $stmt->bindValue(':start_price', $this->data->start_price);
          $stmt->bindValue(':debtor_ITN', $this->data->debtor_ITN);

          $stmt->execute();
      }

      private function createLotsTable() {
          $query = "CREATE TABLE IF NOT EXISTS lots (
              trade_number TEXT PRIMARY KEY,
              email TEXT,
              phone TEXT,
              bankruptcy_case_number TEXT,
              debtor_property_information TEXT,
              start_price REAL,
              debtor_ITN TEXT
          )";

          $this->db->exec($query);
      }
  }

// Инициализация объекта DataProcessor
// $client = new Client(['verify' => false]);
// $dataProcessor = new DataProcessor($client);

// Получение данных из формы
$tradeNumber = $_POST['tradeNumber'] ?? '31710-ОТПП';
$lotNumber = $_POST['lotNumber'] ?? 10;

$dataProcessor = new DataProcessor(new Client(['verify' => false])); // Создаем экземпляр класса DataProcessor
$dataProcessor->processFormData($tradeNumber, $lotNumber);
$db = $dataProcessor->db;

$query = "SELECT * FROM lots";
$result = $db->query($query);



echo "<table border='1'>";
echo "<tr><th>Trade Number</th><th>Email</th><th>Phone</th><th>Bankruptcy Case Number</th><th>Debtor Property Information</th><th>Start Price</th><th>Debtor ITN</th></tr>";

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    echo "<td>".$row['trade_number']."</td>";
    echo "<td>".$row['email']."</td>";
    echo "<td>".$row['phone']."</td>";
    echo "<td>".$row['bankruptcy_case_number']."</td>";
    echo "<td>".$row['debtor_property_information']."</td>";
    echo "<td>".$row['start_price']."</td>";
    echo "<td>".$row['debtor_ITN']."</td>";
    echo "</tr>";
}

echo "</table>";



