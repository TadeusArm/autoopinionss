<?php
session_start();
include 'config/db.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['vehicle_id'])){
    header("Location: index.php");
    exit;
}

$coche_id = $_GET['vehicle_id'];
$mi_id = $_SESSION['user_id'];

$check = $pdo->prepare("SELECT id FROM comments WHERE user_id = ? AND vehicle_id = ?");
$check->execute([$mi_id, $coche_id]);
$ya_he_opinado = $check->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$ya_he_opinado) {
    $nota = $_POST['nota'];
    $comen = $_POST['comentario'];
    $pdo->prepare("INSERT INTO comments (user_id, vehicle_id, content) VALUES (?, ?, ?)")->execute([$mi_id, $coche_id, $comen]);
    $pdo->prepare("INSERT INTO ratings (user_id, vehicle_id, rating) VALUES (?, ?, ?)")->execute([$mi_id, $coche_id, $nota]);
    header("Location: comments.php?vehicle_id=$coche_id#leer");
    exit;
}

$st_c = $pdo->prepare("SELECT v.*, u.username FROM vehicles v JOIN users u ON v.user_id = u.id WHERE v.id = ?");
$st_c->execute([$coche_id]);
$c = $st_c->fetch();

$st_l = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.vehicle_id = ? ORDER BY c.id DESC");
$st_l->execute([$coche_id]);
$lista = $st_l->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Opiniones - AutoOpinions</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Único cambio: Extensión .webp y forzar el fondo diferente aquí */
        body { 
            background: url('assets/img/fondo-comments.webp') center/cover no-repeat fixed !important; 
        }
        .contenedor { max-width: 750px; margin: 40px auto; padding: 0 20px; position: relative; z-index: 2; }
        .bloque { background: rgba(31, 41, 55, 0.75); backdrop-filter: blur(12px); padding: 25px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.15); margin-bottom: 25px; }
        .btn-rojo { background: rgba(239, 68, 68, 0.6); color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; }
        .input-text { width: 100%; padding: 12px; background: rgba(0,0,0,0.3); color: white; border: 1px solid #444; border-radius: 8px; margin: 10px 0; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="contenedor">
        <div style="margin-bottom: 30px;">
            <a href="index.php" class="btn-rojo">← Volver al Muro</a>
        </div>

        <div class="bloque">
            <h2 style="margin:0;"><?php echo htmlspecialchars($c['brand']." ".$c['model']); ?></h2>
            <p style="color: #9ca3af;">De: @<?php echo htmlspecialchars($c['username']); ?></p>
            <?php if($c['image']): ?>
                <img src="<?php echo htmlspecialchars($c['image']); ?>" style="width: 100%; border-radius: 8px; margin: 20px 0;">
            <?php endif; ?>
            <p style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($c['description'])); ?></p>
        </div>

        <div class="bloque">
            <?php if($ya_he_opinado): ?>
                <div style="text-align: center; color:rgba(111, 151, 92, 0.84); font-weight: bold;">Ya has enviado tu opinión. ¡Gracias!</div>
            <?php else: ?>
                <h3 style="margin-top:0;">Danos tu valoración</h3>
                <button onclick="document.getElementById('formu').style.display='block'; this.style.display='none';" style="background:#2563eb; color:white; border:none; padding:12px 20px; border-radius:8px; cursor:pointer; font-weight:bold;">Escribir mi opinión</button>
                <div id="formu" style="display:none; margin-top:20px;">
                    <form method="POST">
                        <label>Nota (1-5):</label><br>
                        <input type="number" name="nota" min="1" max="5" required style="width: 60px; padding: 10px; background:#121212; color:white; border:1px solid #444; border-radius:8px;"><br><br>
                        <label>Tu comentario:</label>
                        <textarea name="comentario" class="input-text" rows="4" required></textarea>
                        <button type="submit" style="background: #10b981; color:white; border:none; padding:12px; border-radius:8px; width:100%; cursor:pointer; font-weight:bold;">Publicar</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="bloque" id="leer">
            <h3 style="margin-top:0; border-bottom: 1px solid #444; padding-bottom: 10px;">Opiniones</h3>
            <?php foreach($lista as $l): ?>
                <div style="border-bottom: 1px solid #333; padding: 15px 0;">
                    <strong style="color: #3b82f6;">@<?php echo htmlspecialchars($l['username']); ?></strong>
                    <p style="margin: 8px 0;"><?php echo nl2br(htmlspecialchars($l['content'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>