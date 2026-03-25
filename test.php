<?php
include 'config/db.php';
$stmt = $pdo->query("SELECT NOW()");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Conexión OK. Fecha del servidor: " . $row['NOW()'];
?>