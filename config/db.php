<?php
$host = 'fdb1028.awardspace.net'; 
$db   = '4590377_autoopinions'; 
$user = '4590377_autoopinions'; 
$pass = 'I}[fJX,Z4*##Bl+d';
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    
} catch (PDOException $e) {
    die("Error de connexió al servidor d'AwardSpace: " . $e->getMessage());
}
?>