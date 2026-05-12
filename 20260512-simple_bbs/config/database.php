<?php
// config/database.php

class Database {
    private $host = 'localhost';
    private $db_name = 'bbs_db';
    private $username = 'root'; // XAMPPのデフォルト
    private $password = '';     // XAMPPのデフォルト
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
                ]
            );
        } catch(PDOException $exception) {
            echo "接続エラー: " . $exception->getMessage();
        }
        return $this->conn;
    }
}