<?php

namespace App;

class DatabaseHandler
{

    private $db;

    public static function getConfig()
    {
        return require './config.php';
    }

    public function __construct()
    {
        $config = self::getConfig();
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']}";
        $this->db = new \PDO($dsn, $config['username'], $config['password']);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->createLotsTable();
    }

    public function insertLot($data)
    {
        $query = "INSERT INTO lots (lot_number, trade_number, href, email, phone, bankruptcy_case_number, debtor_property_information, start_price, debtor_ITN, date_auction)
              VALUES (:lot_number, :trade_number, :href, :email, :phone, :bankruptcy_case_number, :debtor_property_information, :start_price, :debtor_ITN, :date_auction)
              ON DUPLICATE KEY UPDATE
              lot_number = VALUES(lot_number),
              trade_number = VALUES(trade_number),
              href = VALUES(href),
              email = VALUES(email),
              phone = VALUES(phone),
              debtor_property_information = VALUES(debtor_property_information),
              start_price = VALUES(start_price),
              debtor_ITN = VALUES(debtor_ITN),
              date_auction = VALUES(date_auction)";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':lot_number' => $data->lot_number,
            ':trade_number' => $data->trade_number,
            ':href' => $data->href,
            ':email' => $data->email,
            ':phone' => $data->phone,
            ':bankruptcy_case_number' => $data->bankruptcy_case_number,
            ':debtor_property_information' => $data->debtor_property_information,
            ':start_price' => $data->start_price,
            ':debtor_ITN' => $data->debtor_ITN,
            ':date_auction' => $data->date_auction,
        ]);
    }

    private function createLotsTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS lots (
            id INT PRIMARY KEY AUTO_INCREMENT,
            lot_number int,
            trade_number VARCHAR(255),
            href VARCHAR(255),
            email VARCHAR(255),
            phone VARCHAR(20),
            bankruptcy_case_number VARCHAR(50),
            debtor_property_information TEXT,
            start_price DECIMAL(10, 2),
            debtor_ITN VARCHAR(20),
            date_auction VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_lot_bankruptcy (lot_number, bankruptcy_case_number)
        )";

        $this->db->exec($query);
    }

    public function lotExists($lotNumber, $tradeNumber)
    {
        $query = "SELECT COUNT(*) FROM lots
              WHERE lot_number = :lot_number
              AND trade_number = :trade_number";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':lot_number' => $lotNumber,
            ':trade_number' => $tradeNumber,
        ]);

        return $stmt->fetchColumn() > 0;
    }

    public function selectLots()
    {
        $query = "SELECT * FROM lots";
        return $this->db->query($query);
    }
}