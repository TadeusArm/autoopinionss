<?php
// Datos extraídos de tus capturas de IONOS
$host = 'tus datos de host'; // Por ejemplo, '
$db   = 'tus datos de bd'; // <-- CAMBIA ESTO (Antes tenías dbs13735105)
$user = 'tu usuario'; 
$pass = 'TucontraseñaSegura'; // Cambia esto por tu contraseña real

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // Configurar el modo de error de PDO a excepción
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si falla, nos dirá el motivo real
    die("Error de conexión: " . $e->getMessage());
}