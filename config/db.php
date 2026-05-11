<?php
/* --- CONFIGURACIÓN PARA XAMPP (Local) ---
$host = 'localhost';
$db   = 'autoopinions';
$user = 'root';
$pass = '';
*/

// --- CONFIGURACIÓN PARA INFINITYFREE  ---
$host = 'sql310.infinityfree.com'; 
$db   = 'if0_41890386_autoopinions'; 
$user = 'if0_41890386';
$pass = 'HCOcYgVzTCnF74';

try {
    
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    
} catch (PDOException $e) {
    die("Error crítico de conexión. Por favor, inténtelo más tarde.");
}
?>