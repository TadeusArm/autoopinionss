<?php
// CONFIGURACIÓN PARA XAMPP
$host = 'localhost';
$db   = 'autoopinions';
$user = 'root';
$pass = '';

/* CONFIGURACIÓN PARA INFINITYFREE 
$host = 'sqlXXX.infinityfree.com'; // Me lo da el panel de Infinity
$db   = 'if0_XXXXXX_autoopinions';
$user = 'if0_XXXXXX';
$pass = 'TuPasswordDeInfinity';
*/

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>