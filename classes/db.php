<?php
function connectToDataBase(){
    $username = 'root';
    $password = '';
    $dbname = 'api_laravel';
    $host = 'localhost';

    $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8;';

    try {
        $conn = new PDO($dsn, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}


