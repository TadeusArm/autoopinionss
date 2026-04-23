<?php
session_start();
include 'config/db.php';
// Asegúrate de tener este archivo para el email
require_once 'includes/functions_mail.php'; 

if(!isset($_SESSION['user_id']) || !isset($_GET['vehicle_id'])){
    header("Location: index.php");
    exit;
}

$coche_id = $_GET['vehicle_id'];
$mi_id = $_SESSION['user_id'];

// Comprobar si ya opinó
$check = $pdo->prepare("SELECT id FROM comments WHERE user_id = ? AND vehicle_id = ?");
$check->execute([$mi_id, $coche_id]);
$ya_he_opinado = $check->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$ya_he_opinado) {
    // Aquí iría tu lógica de inserción (estrellas y comentario) y envío de email
    // ...
    header("Location: comments.php?vehicle_id=$coche_id#leer");
    exit;
}

// Obtener datos del coche
$st_c = $pdo->prepare("SELECT v.*, u.username FROM vehicles v JOIN users u ON v.user_id = u.id WHERE v.id = ?");
$st_c->execute([$coche_id]);
$c = $st_c->fetch();

// Lista de comentarios con JOIN a ratings (para tener la nota)
$st_l = $pdo->prepare("
    SELECT c.*, u.username, r.rating 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    LEFT JOIN ratings r ON (r.user_id = c.user_id AND r.vehicle_id = c.vehicle_id)
    WHERE c.vehicle_id = ? 
    ORDER BY c.id DESC
");
$st_l->execute([$coche_id]);
$lista = $st_l->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Opiniones - AutoOpinions</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { 
            background: url('assets/img/fondo-comments.webp') center/cover no-repeat fixed !important; 
            margin: 0; padding: 0;
        }

        .contenedor-comentarios { 
            max-width: 800px; /* Un poco más ancho para la preview lateral */
            margin: 0 auto; 
            padding: 20px; 
            position: relative; 
            z-index: 2; 
        }

        .bloque-glass { 
            background: rgba(17, 24, 39, 0.75); /* Un poco más oscuro para mejor contraste */
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            padding: 25px; 
            border-radius: 16px; 
            margin-bottom: 20px; 
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* --- NUEVO: PREVISUALIZACIÓN DEL COCHE --- */
        .coche-preview-header {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .img-preview {
            width: 150px; /* Tamaño de la miniatura en PC */
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.2);
            flex-shrink: 0; /* Evita que se encoja */
            cursor: pointer;
            transition: 0.3s;
        }
        .img-preview:hover { transform: scale(1.05); }

        /* Botón de volver con estilo Glass */
        .btn-volver { 
            background: rgba(239, 68, 68, 0.15); 
            color: #fca5a5; 
            padding: 10px 18px; 
            text-decoration: none; 
            border-radius: 10px; 
            font-weight: bold; 
            display: inline-block; 
            margin-bottom: 15px;
            transition: 0.3s;
            border: 1px solid rgba(239, 68, 68, 0.2);
            font-size: 0.9rem;
        }
        .btn-volver:hover { background: rgba(239,68,68,0.3); color: white; }

        /* Estrellas Interactivas */
        .rating-stars { display: flex; flex-direction: row-reverse; justify-content: flex-end; margin: 10px 0; }
        .rating-stars input { display: none; }
        .rating-stars label {
            font-size: 2.5rem; color: rgba(255, 255, 255, 0.2);
            cursor: pointer; transition: 0.2s; margin-right: 5px;
        }
        .rating-stars input:checked ~ label,
        .rating-stars label:hover,
        .rating-stars label:hover ~ label {
            color: #fbbf24;
            text-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
        }

        textarea {
            width: 100%; background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px;
            padding: 12px; color: white; box-sizing: border-box; font-family: inherit; resize: none;
        }

        button { 
            width: 100%; padding: 14px; border-radius: 10px; border: none; 
            background: #10b981; color: white; font-weight: bold; font-size: 1rem; cursor: pointer;
        }

        /* Responsive Móvil */
        @media (max-width: 768px) {
            .contenedor-comentarios { padding: 10px; }
            .bloque-glass { 
                border-radius: 0; margin-left: -10px; margin-right: -10px; 
                border-left: none; border-right: none;
                padding: 15px;
            }
            .coche-preview-header { flex-direction: column; text-align: center; }
            .img-preview { width: 100%; height: 180px; } /* Banner en móvil */
            h2 { font-size: 1.5rem; }
            .rating-stars label { font-size: 3rem; }
            .btn-volver { width: 100%; text-align: center; box-sizing: border-box; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="overlay"></div>

    <div class="contenedor-comentarios">
        <a href="index.php" class="btn-volver">← Volver al Muro</a>

        <div class="bloque-glass">
    <div class="coche-preview-header">
        <?php if($c['image']): ?>
            <a href="assets/img/vehicles/<?php echo htmlspecialchars($c['image']); ?>" target="_blank">
                <img src="assets/img/vehicles/<?php echo htmlspecialchars($c['image']); ?>" 
                     class="img-preview" 
                     alt="Coche"
                     onerror="this.src='https://via.placeholder.com/150x100?text=AutoOpinions';">
            </a>
        <?php else: ?>
            <img src="assets/img/no-foto.webp" class="img-preview" alt="Sin foto">
        <?php endif; ?>
        
        <div>
            <h2 style="margin:0; color: #60a5fa;"><?php echo htmlspecialchars($c['brand']." ".$c['model']); ?></h2>
            <p style="color: #9ca3af; margin: 5px 0;">Publicado por: <b>@<?php echo htmlspecialchars($c['username']); ?></b></p>
            <p style="line-height: 1.5; margin-top: 10px; font-size: 0.9rem; color: #d1d5db;">
                <?php 
                echo nl2br(htmlspecialchars(mb_strimwidth($c['description'], 0, 150, "..."))); 
                ?>
            </p>
        </div>
    </div>
</div>

        <div class="bloque-glass">
            <?php if($ya_he_opinado): ?>
                <div style="text-align: center; color:#4ade80; font-weight: bold;">✓ Ya has valorado este vehículo.</div>
            <?php else: ?>
                <h3 style="margin-top:0;">Danos tu valoración</h3>
                <form method="POST">
                    <label>Puntuación:</label>
                    <div class="rating-stars">
                        <input type="radio" id="star5" name="nota" value="5" required /><label for="star5">★</label>
                        <input type="radio" id="star4" name="nota" value="4" /><label for="star4">★</label>
                        <input type="radio" id="star3" name="nota" value="3" /><label for="star3">★</label>
                        <input type="radio" id="star2" name="nota" value="2" /><label for="star2">★</label>
                        <input type="radio" id="star1" name="nota" value="1" /><label for="star1">★</label>
                    </div>
                    <textarea name="comentario" placeholder="¿Qué te parece este coche?" required style="height: 100px; margin-top: 10px;"></textarea>
                    <button type="submit" style="margin-top: 15px;">Publicar Opinión</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="bloque-glass" id="leer">
            <h3 style="margin-top:0; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">Opiniones de la comunidad</h3>
            <?php foreach($lista as $l): ?>
                <div style="border-bottom: 1px solid rgba(255,255,255,0.05); padding: 15px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong style="color: #60a5fa;">@<?php echo htmlspecialchars($l['username']); ?></strong>
                        <span style="color: #fbbf24; letter-spacing: 2px;">
                            <?php 
                            for($i=1; $i<=5; $i++){
                                echo ($i <= $l['rating']) ? '★' : '☆';
                            }
                            ?>
                        </span>
                    </div>
                    <p style="margin: 10px 0 0 0; color: #e2e8f0; line-height: 1.5;">
                        <?php echo nl2br(htmlspecialchars($l['content'])); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>