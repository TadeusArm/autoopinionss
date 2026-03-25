<?php
$host = "localhost";
$db   = "autoopinions";
$user = "root"; // tu usuario MySQL
$pass = "";     // tu contraseña MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa"; // Solo para probar
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>