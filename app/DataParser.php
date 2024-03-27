<?php

namespace App;

use DiDom\Document;
use GuzzleHttp\Client;

class DataParser
{
    private Client $client;
    public \stdClass $data;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->data = new \stdClass();
    }

    public function processFormData(string $tradeNumber, int $lotNumber)
    {
        $url = "https://nistp.ru/?trade_number=$tradeNumber&lot_number=$lotNumber";
        $this->data->trade_number = $tradeNumber;

        $pageTradeList = $this->getDocumentFromUrl($url);

        $bankrotHref = $this->getLinkBankrot($pageTradeList);

        if (!$bankrotHref) {
            echo "Лот не найден";
            die();
        }

        $pageTradeView = $this->getDocumentFromUrl($bankrotHref);

        $this->processContactPersontData($pageTradeView->find('.node_view tr'));
        $this->processLotData($pageTradeView->find("table#table_lot_$lotNumber tr"));
        $this->processBankrotData($pageTradeView->find('table.node_view'));
        return $this->data;
    }

    private function getLinkBankrot(Document $document): ?string
    {
        $links = $document->find('table.data tbody tr td a');
        if (!empty($links)) {
            $link = $links[0]->attr('href');
            if ($link) {
                $this->data->href = $link;
                return $link;
            }
        }
        return null;
    }

    private function getDocumentFromUrl(string $url): Document
    {
        $response = $this->client->get($url);
        $html = $response->getBody()->getContents();
        $document = new Document();
        $document->loadHtml($html);
        return $document;
    }

    private function processContactPersontData(array $rows): void
    {
        $labelMappings = [
            'E-mail' => 'email',
            'Телефон' => 'phone',
            'Номер дела о банкротстве' => 'bankruptcy_case_number',
            'Дата проведения' => 'date_auction',
        ];

        $this->processData($rows, $labelMappings);
    }

    private function processLotData(array $rows): void
    {
        $labelMappings = [
            'Номер лота' => 'lot_number',
            'Cведения об имуществе (предприятии) должника, выставляемом на торги, его составе, характеристиках, описание' => 'debtor_property_information',
            'Начальная цена' => 'start_price',
        ];

        $this->processData($rows, $labelMappings);
    }

    private function processData(array $rows, array $labelMappings): void
    {
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

    private function processBankrotData(array $tables): void
    {
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

    private function processDebtorITN(array $rows): void
    {
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
}
