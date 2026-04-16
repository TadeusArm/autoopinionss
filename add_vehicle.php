<?php
session_start();
include 'config/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year  = $_POST['year'];
    $km    = $_POST['km']; 
    $user_id = $_SESSION['user_id'];

    // Gestión de imagen
    $image_name = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . "_" . $user_id . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "assets/img/vehicles/" . $image_name);
    }

    // INSERT ACTUALIZADO
    $sql = "INSERT INTO vehicles (brand, model, year, km, image, user_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$brand, $model, $year, $km, $image_name, $user_id])) {
        header("Location: index.php");
        exit;
    } else {
        $mensaje = "Error al subir el vehículo.";
    }
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
    <?php include 'includes/header.php'; ?>

    <div class="card" style="max-width: 500px; margin: 50px auto;">
        <h2 style="text-align: center; margin-bottom: 25px;">Subir nuevo vehículo</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="input-group" style="margin-bottom: 15px;">
                <label style="color: #94a3b8; font-size: 0.8rem;">Marca</label>
                <input type="text" name="brand" placeholder="Ej: BMW" required>
            </div>

            <div class="input-group" style="margin-bottom: 15px;">
                <label style="color: #94a3b8; font-size: 0.8rem;">Modelo</label>
                <input type="text" name="model" placeholder="Ej: M3" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="input-group">
                    <label style="color: #94a3b8; font-size: 0.8rem;">Año</label>
                    <input type="number" name="year" placeholder="2024" required>
                </div>
                <div class="input-group">
                    <label style="color: #94a3b8; font-size: 0.8rem;">Kilómetros</label>
                    <input type="number" name="km" placeholder="Ej: 50000" required>
                </div>
            </div>

            <div class="input-group" style="margin-bottom: 25px;">
                <label style="color: #94a3b8; font-size: 0.8rem;">Foto del coche</label>
                <input type="file" name="image" accept="image/*" required>
            </div>

            <button type="submit" class="btn-submit">Publicar coche</button>
            <a href="index.php" style="display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; font-size: 0.9rem;">Cancelar</a>
        </form>
    </div>
</body>
</html>