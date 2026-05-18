<?php
header('Content-Type: text/plain');
echo "DB Debug Information\n";
echo "====================\n";
echo "DB_HOST: " . getenv('DB_HOST') . "\n";
echo "DB_PORT: " . getenv('DB_PORT') . "\n";
echo "DB_DATABASE: " . getenv('DB_DATABASE') . "\n";
echo "DB_USERNAME: " . getenv('DB_USERNAME') . "\n";

$host = getenv('DB_HOST');
if ($host) {
    echo "DNS Resolution (gethostbyname): " . gethostbyname($host) . "\n";
    
    try {
        $dsn = "mysql:host=$host;port=" . getenv('DB_PORT') . ";dbname=" . getenv('DB_DATABASE');
        $pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        echo "Connection: SUCCESS\n";
    } catch (Exception $e) {
        echo "Connection: FAILED\n";
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "DB_HOST is empty!\n";
}
