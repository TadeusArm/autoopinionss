<?php
// Datos extraídos de tus capturas de IONOS
$host = 'db5020437286.hosting-data.io'; 
$db   = 'dbs15660988'; // <-- CAMBIA ESTO (Antes tenías dbs13735105)
$user = 'dbu2367501'; 
$pass = 'Tadeuossanbaudelio29032006*'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // Configurar el modo de error de PDO a excepción
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si falla, nos dirá el motivo real
    die("Error de conexión: " . $e->getMessage());
}