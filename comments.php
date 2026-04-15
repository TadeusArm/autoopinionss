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
        body { 
            background: url('assets/img/fondo-comments.webp') center/cover no-repeat fixed !important; 
        }
        /* Ajuste para bajar el botón de volver */
        .contenedor-comentarios { 
            max-width: 750px; 
            margin: 0 auto; 
            padding: 40px 20px; /* Aumentamos el padding superior */
            position: relative; 
            z-index: 2; 
        }
        .bloque-glass { 
            background: rgba(31, 41, 55, 0.75); 
            backdrop-filter: blur(12px); 
            padding: 25px; 
            border-radius: 12px; 
            border: 0px solid rgba(255,255,255,0.15); 
            margin-bottom: 25px; 
            color: white;
        }
        .btn-volver { 
            background: rgba(239, 68, 68, 0.2); 
            color: #fca5a5; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: bold; 
            display: inline-block; 
            border: 0px solid rgba(239,68,68,0.3);
            margin-bottom: 20px;
            transition: 0.3s;
        }
        .btn-volver:hover { background: rgba(239,68,68,0.4); color: white; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="overlay"></div>

    <div class="contenedor-comentarios">
        <div>
            <a href="index.php" class="btn-volver">← Volver al Muro</a>
        </div>

        <div class="bloque-glass">
            <h2 style="margin:0; color: #3b82f6;"><?php echo htmlspecialchars($c['brand']." ".$c['model']); ?></h2>
            <p style="color: #9ca3af;">Publicado por: @<?php echo htmlspecialchars($c['username']); ?></p>
            <?php if($c['image']): ?>
                <img src="<?php echo htmlspecialchars($c['image']); ?>" style="width: 100%; border-radius: 8px; margin: 20px 0; border: 1px solid rgba(255,255,255,0.1);">
            <?php endif; ?>
            <p style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($c['description'])); ?></p>
        </div>

        <div class="bloque-glass">
            <?php if($ya_he_opinado): ?>
                <div style="text-align: center; color:#4ade80; font-weight: bold;">✓ Ya has enviado tu opinión.</div>
            <?php else: ?>
                <h3 style="margin-top:0;">Danos tu valoración</h3>
                <form method="POST">
                    <label>Nota (1-5):</label><br>
                    <input type="number" name="nota" min="1" max="5" required style="width: 70px; margin: 10px 0;">
                    <br>
                    <label>Tu comentario:</label>
                    <textarea name="comentario" placeholder="Escribe aquí..." required style="height: 100px; margin-top: 10px;"></textarea>
                    <button type="submit" style="margin-top: 15px; background: #10b981;">Publicar Opinión</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="bloque-glass" id="leer">
            <h3 style="margin-top:0; border-bottom: 0px solid rgba(255,255,255,0.1); padding-bottom: 10px;">Opiniones de la comunidad</h3>
            <?php foreach($lista as $l): ?>
                <div style="border-bottom: 0px solid rgba(255,255,255,0.05); padding: 15px 0;">
                    <strong style="color: #60a5fa;">@<?php echo htmlspecialchars($l['username']); ?></strong>
                    <p style="margin: 8px 0; color: #e2e8f0;"><?php echo nl2br(htmlspecialchars($l['content'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>