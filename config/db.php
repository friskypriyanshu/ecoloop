<?php
// --- DATABASE CONNECTION ---

$host = '127.0.0.1';       // or 'localhost'
$dbname = 'ecoloop_db';    // The database name you created in phpMyAdmin
$username = 'root';        // Default username for XAMPP
$password = '';            // Default password for XAMPP is empty
$charset = 'utf8mb4';

// This part creates the connection string
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// These are standard options for the database connection
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // This is the line that actually tries to connect to the database
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // If connection fails, it will show an error.
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>