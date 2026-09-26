<?php
// Database Configuration File (config.php)
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'marketlink_db'; 

// 1. MySQLi Connection
$con = mysqli_connect($host, $username, $password, $dbname);
if (!$con) {
    die("MySQLi Connection Failed: " . mysqli_connect_error());
}

// 2. PDO Connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("PDO Connection Failed: " . $e->getMessage());
}
?>