<?php
declare(strict_types=1);

// $DB_HOST = '127.0.0.1';
// $DB_NAME = 'webtech_reg';
// $DB_USER = 'root';
// $DB_PASS = '';
// $DB_DSN  = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

$DB_HOST = getenv('RDS_HOSTNAME');
$DB_USER = getenv('RDS_USERNAME');
$DB_PASS = getenv('RDS_PASSWORD');
$DB_NAME = getenv('RDS_DB_NAME');
$DB_PORT = getenv('RDS_PORT') ?: 3306;
$DB_DSN  = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4";


$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}
session_start();
