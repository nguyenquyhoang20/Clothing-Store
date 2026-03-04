<?php
    // Simple .env parser
    $envPath = __DIR__ . '/../.env';
    $envVars = file_exists($envPath) ? parse_ini_file($envPath) : [];

    $host = $envVars['DB_HOST'] ?? "localhost";
    $username = $envVars['DB_USER'] ?? "root";
    $password = $envVars['DB_PASS'] ?? "";
    $database = $envVars['DB_NAME'] ?? "nhom11ltw";

    // MySQLi connection (legacy support)
    $conn=mysqli_connect($host, $username, $password, $database);
    mysqli_set_charset($conn,'utf8');
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    // Check database
    if(!$conn)
    {
        die("Connection Failed ". mysqli_connect_errno());
    }

    // PDO connection (prepared statements)
    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$database;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        die("PDO Connection Failed: " . $e->getMessage());
    }
?>