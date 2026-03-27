<?php
session_start();
include 'config/db.php';

if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $desc = $_POST['description'];
    $user_id = $_SESSION['user_id'];
    
    $img_path = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $folder = "assets/img/vehicles/";
        if (!file_exists($folder)) { mkdir($folder, 0777, true); }
        $img_path = $folder . time() . "_" . $_FILES["image"]["name"];
        move_uploaded_file($_FILES["image"]["tmp_name"], $img_path);
    }

    $stmt = $pdo->prepare("INSERT INTO vehicles (user_id, brand, model, year, description, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $brand, $model, $year, $desc, $img_path]);
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Coche - AutoOpinions</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="overlay"></div>
    <div class="card">
        <h2>Nuevo Vehículo</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="brand" placeholder="Marca" required style="margin-bottom:10px;">
            <input type="text" name="model" placeholder="Modelo" required style="margin-bottom:10px;">
            <input type="number" name="year" placeholder="Año" required style="margin-bottom:10px;">
            <textarea name="description" placeholder="Descripción..." required style="width:100%; height:80px; margin-bottom:10px;"></textarea>
            <input type="file" name="image" accept="image/*" style="margin-bottom:15px;">
            <button type="submit">Publicar Coche</button>
        </form>
        <p style="text-align:center; margin-top:15px;"><a href="index.php" style="color:white;">Cancelar</a></p>
    </div>
</body>
</html>