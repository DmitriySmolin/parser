<?php
require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use App\DataParser;
use App\DatabaseHandler;


// Получение данных из формы
$tradeNumber = $_POST['tradeNumber'];
$lotNumber = $_POST['lotNumber'];


$client = new Client(['verify' => false]);
$dataParser = new DataParser($client);
$databaseHandler = new DatabaseHandler();
$dataParser->processFormData($tradeNumber, $lotNumber);


if ($databaseHandler->lotExists($lotNumber, $tradeNumber)) {
    echo "Данные успешно обновлены";
    $databaseHandler->insertLot($dataParser->data);
    return false;
}

if (!empty((array)$dataParser->data)) {
    $databaseHandler->insertLot($dataParser->data);
    echo "Данные успешно сохранены";
} else {
    die;
}









