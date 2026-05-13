<?php
session_start();
include 'config/db.php';
require_once 'includes/functions_mail.php'; 

// Forzamos modo hosting para evitar que salgan rutas internas si algo falla
$modo_hosting = true; 

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

// --- LÓGICA DE INSERCIÓN Y EMAIL ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$ya_he_opinado) {
    $nota = $_POST['nota'] ?? null;
    $comentario = trim($_POST['comentario'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

    if ($nota && !empty($comentario)) {
        try {
            $pdo->beginTransaction();

            // 1. Insertar comentario
           $ins_comm = $pdo->prepare("INSERT INTO comments (user_id, vehicle_id, content, parent_id) VALUES (?, ?, ?, ?)");
    $ins_comm->execute([$mi_id, $coche_id, $comentario, $parent_id]);

            // 2. Insertar estrellas (rating)
            $ins_rate = $pdo->prepare("INSERT INTO ratings (user_id, vehicle_id, rating) VALUES (?, ?, ?)");
            $ins_rate->execute([$mi_id, $coche_id, $nota]);

            $pdo->commit();

            // 3. Envío de email al dueño del coche (Silencioso)
            try {
                $st_owner = $pdo->prepare("SELECT v.brand, v.model, u.email, u.username 
                                         FROM vehicles v 
                                         JOIN users u ON v.user_id = u.id 
                                         WHERE v.id = ?");
                $st_owner->execute([$coche_id]);
                $owner = $st_owner->fetch();

                if($owner && function_exists('enviarNotificacionEmail')) {
                    enviarNotificacionEmail(
                        $owner['email'], 
                        $owner['username'], 
                        'comment', 
                        $owner['brand'] . " " . $owner['model']
                    );
                }
            } catch (Exception $e_mail) {
                // Si el mail falla, no hacemos nada para que el usuario no vea el error
            }

            header("Location: comments.php?vehicle_id=$coche_id#leer");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            // Error crítico de DB: redirigir a una página de error o al index
            header("Location: index.php?error=db");
            exit;
        }
    }
}

// Obtener datos del coche para mostrar arriba
$st_c = $pdo->prepare("SELECT v.*, u.username FROM vehicles v JOIN users u ON v.user_id = u.id WHERE v.id = ?");
$st_c->execute([$coche_id]);
$c = $st_c->fetch();

// Lista de comentarios con JOIN a ratings
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
    <link rel="icon" type="image/png" href="assets/img/favicon.jpg">
    <style>
        body { 
            background: url('assets/img/fondo-comments.webp') center/cover no-repeat fixed !important; 
            margin: 0; padding: 0;
        }
        .contenedor-comentarios { max-width: 800px; margin: 0 auto; padding: 20px; position: relative; z-index: 2; }
        .bloque-glass { 
            background: rgba(17, 24, 39, 0.75); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            padding: 25px; border-radius: 16px; margin-bottom: 20px; color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .coche-preview-header { display: flex; gap: 20px; align-items: center; }
        .img-preview { 
            width: 150px; height: 100px; object-fit: cover; border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.2); flex-shrink: 0; cursor: pointer; transition: 0.3s;
        }
        .img-preview:hover { transform: scale(1.05); }
        .btn-volver { 
            background: rgba(239, 68, 68, 0.15); color: #fca5a5; padding: 10px 18px; 
            text-decoration: none; border-radius: 10px; font-weight: bold; display: inline-block; 
            margin-bottom: 15px; transition: 0.3s; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.9rem;
        }
        .btn-volver:hover { background: rgba(239,68,68,0.3); color: white; }
        .rating-stars { display: flex; flex-direction: row-reverse; justify-content: flex-end; margin: 10px 0; }
        .rating-stars input { display: none; }
        .rating-stars label { font-size: 2.5rem; color: rgba(255, 255, 255, 0.2); cursor: pointer; transition: 0.2s; margin-right: 5px; }
        .rating-stars input:checked ~ label, .rating-stars label:hover, .rating-stars label:hover ~ label { color: #fbbf24; text-shadow: 0 0 10px rgba(251, 191, 36, 0.5); }
        textarea { width: 100%; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px; padding: 12px; color: white; box-sizing: border-box; resize: none; }
        button { width: 100%; padding: 14px; border-radius: 10px; border: none; background: #10b981; color: white; font-weight: bold; font-size: 1rem; cursor: pointer; }
        @media (max-width: 768px) {
            .contenedor-comentarios { padding: 10px; }
            .coche-preview-header { flex-direction: column; text-align: center; }
            .img-preview { width: 100%; height: 180px; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="contenedor-comentarios">
        <a href="index.php" class="btn-volver">← Volver al Muro</a>

        <div class="bloque-glass">
            <div class="coche-preview-header">
                <?php if($c['image']): ?>
                    <a href="assets/img/vehicles/<?php echo htmlspecialchars($c['image']); ?>" target="_blank">
                        <img src="assets/img/vehicles/<?php echo htmlspecialchars($c['image']); ?>" class="img-preview" alt="Coche">
                    </a>
                <?php else: ?>
                    <img src="assets/img/no-foto.webp" class="img-preview" alt="Sin foto">
                <?php endif; ?>
                <div>
                    <h2 style="margin:0; color: #60a5fa;"><?php echo htmlspecialchars($c['brand']." ".$c['model']); ?></h2>
                    <p style="color: #9ca3af; margin: 5px 0;">Publicado por: <b>@<?php echo htmlspecialchars($c['username']); ?></b></p>
                </div>
            </div>
        </div>

        <div class="bloque-glass">
            <?php if($ya_he_opinado): ?>
                <div style="text-align: center; color:#4ade80; font-weight: bold;">✓ Ya has valorado este vehículo.</div>
            <?php else: ?>
                <h3 style="margin-top:0;">Danos tu valoración</h3>
                <form method="POST">
                    <div class="rating-stars">
                        <input type="radio" id="star5" name="nota" value="5" required /><label for="star5">★</label>
                        <input type="radio" id="star4" name="nota" value="4" /><label for="star4">★</label>
                        <input type="radio" id="star3" name="nota" value="3" /><label for="star3">★</label>
                        <input type="radio" id="star2" name="nota" value="2" /><label for="star2">★</label>
                        <input type="radio" id="star1" name="nota" value="1" /><label for="star1">★</label>
                    </div>
                    <textarea name="comentario" placeholder="¿Qué te parece este coche?" required style="height: 100px;"></textarea>
                    <button type="submit" style="margin-top: 15px;">Publicar Opinión</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="bloque-glass" id="leer">
            <h3 style="margin-top:0;">Opiniones de la comunidad</h3>
            <?php foreach($lista as $l): 
    // Si tiene parent_id, es una respuesta
    $es_respuesta = !empty($l['parent_id']);
?>
    <div style="
        border-bottom: 1px solid rgba(255,255,255,0.05); 
        padding: 15px 0; 
        margin-left: <?= $es_respuesta ? '40px' : '0' ?>; 
        border-left: <?= $es_respuesta ? '2px solid #3b82f6' : 'none' ?>;
        padding-left: <?= $es_respuesta ? '15px' : '0' ?>;">
        
        <strong>@<?php echo htmlspecialchars($l['username']); ?></strong>
        
        <?php if(!$es_respuesta): ?>
            <span style="color: #fbbf24; float: right;">
                <?php for($i=1; $i<=5; $i++) echo ($i <= $l['rating']) ? '★' : '☆'; ?>
            </span>
        <?php endif; ?>
        
        <p><?php echo nl2br(htmlspecialchars($l['content'])); ?></p>
        
        <button onclick="document.getElementById('form-<?= $l['id'] ?>').style.display='block'" 
                style="background:none; border:none; color:#60a5fa; font-size: 0.75rem; cursor:pointer;">
            Responder
        </button>

        <form id="form-<?= $l['id'] ?>" method="POST" style="display:none; margin-top:10px;">
            <input type="hidden" name="parent_id" value="<?= $l['id'] ?>">
            <textarea name="comentario" required placeholder="Escribe tu respuesta..."></textarea>
            <button type="submit" style="font-size: 0.8rem; padding: 5px;">Enviar</button>
        </form>
    </div>
<?php endforeach; ?>
        </div>
    </div>
</body>
</html>