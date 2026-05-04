<?php
// includes/db.php

// Read from Environment Variables (set by Render or your local server)
$host     = getenv('DB_HOST') ?: 'localhost';
$port     = getenv('DB_PORT') ?: '3306';
$dbname   = getenv('DB_NAME') ?: 'finance_system';
$user     = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Check if the Aiven CA Certificate exists in the includes folder.
// If it does, strictly enforce SSL to connect to Aiven safely.
$certPath = __DIR__ . '/ca.pem';
if (file_exists($certPath)) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $certPath;
    
    // NOTE: If you still get a "certificate verify failed" error after this, 
    // you can safely uncomment the line below to bypass the strict hostname check.
    // $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $password, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
