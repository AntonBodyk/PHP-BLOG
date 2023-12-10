<?php
namespace DataBaseClass\Connection;



use PDO;
use PDOException;

class DataBase
{
    private $conn;

    public function __construct()
    {
        $this->connectToDataBase();
    }

    private function connectToDataBase()
    {
        $username = 'root';
        $password = '';
        $dbname = 'api_laravel';
        $host = 'localhost';

        $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8;';

        try {
            $this->conn = new PDO($dsn, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        if ($this->conn) {
            return $this->conn;
        } else {
            throw new \Exception("No database connection available.");
        }
    }
}
