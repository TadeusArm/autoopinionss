<?php
session_start();
include 'config/db.php';

// Si no hay sesión, redirigir al login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Consulta para obtener vehículos, el nombre del dueño, nota media y total de comentarios
$sql = "SELECT v.*, u.username, 
        (SELECT COUNT(*) FROM comments WHERE vehicle_id = v.id) as total_comentarios,
        (SELECT AVG(rating) FROM ratings WHERE vehicle_id = v.id) as nota_media
        FROM vehicles v 
        JOIN users u ON v.user_id = u.id 
        ORDER BY v.id DESC";
$query = $pdo->query($sql);
$coches = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoOpinions - Muro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* Fondo específico para el Index */
        body { 
            background: url('assets/img/fondo-index.jpg') center/cover no-repeat fixed !important; 
        }
        
        /* Contenedor del Feed más estrecho para mejor lectura */
        .feed-container {
            position: relative;
            z-index: 2;
            max-width: 650px; 
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Imágenes más bajas y estilizadas */
        .img-wrapper {
            width: 100%;
            height: 300px; /* Altura reducida para que no sea gigante */
            overflow: hidden;
            border-radius: 12px;
            margin: 12px 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.3);
        }

        .img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .stats-badge {
            background: rgba(0, 0, 0, 0.5);
            padding: 8px 12px;
            border-radius: 8px;
            display: inline-block;
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.85rem;
            margin-bottom: 8px;
        }

        .btn-azul {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 12px;
            display: block;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            font-size: 0.95rem;
        }

        .btn-gris {
            background: rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            padding: 12px;
            display: block;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.95rem;
        }

        .btn-azul:hover, .btn-gris:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }

        .vehicle-card {
            margin-bottom: 40px; /* Espacio entre publicaciones */
        }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <?php include 'includes/header.php'; ?>

    <div class="feed-container">
        <h2 style="border-left: 5px solid #3b82f6; padding-left: 15px; margin-bottom: 25px; text-shadow: 2px 2px 5px rgba(0,0,0,0.7); font-size: 1.6rem;">
            Muro de la Comunidad
        </h2>

        <?php if(count($coches) > 0): ?>
            <?php foreach($coches as $c): ?>
                <?php 
                    // Comprobar si el usuario ya ha opinado
                    $st = $pdo->prepare("SELECT id FROM comments WHERE user_id = ? AND vehicle_id = ?");
                    $st->execute([$user_id, $c['id']]);
                    $ya_opino = $st->fetch();

                    // Lógica inteligente para la ruta de la imagen
                    $img_db = $c['image'];
                    if (strpos($img_db, 'assets/') === 0) {
                        $ruta_final = $img_db;
                    } else {
                        $ruta_final = "assets/img/vehicles/" . $img_db;
                    }
                ?>
                
                <div class="vehicle-card">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <div>
                            <span style="color: #9ca3af; font-size: 0.8rem;">Publicado por</span>
                            <strong style="color: #3b82f6; display: block; font-size: 1rem;">@<?php echo htmlspecialchars($c['username']); ?></strong>
                        </div>
                        <span style="color: #6b7280; font-size: 0.9rem; font-weight: bold;"><?php echo htmlspecialchars($c['year']); ?></span>
                    </div>

                    <h3 style="margin: 10px 0; font-size: 1.6rem; letter-spacing: -0.5px; color: #f8fafc;">
                        <?php echo htmlspecialchars($c['brand'] . " " . $c['model']); ?>
                    </h3>
                    
                    <div class="img-wrapper">
                        <?php if(!empty($img_db)): ?>
                            <img src="<?php echo htmlspecialchars($ruta_final); ?>" alt="Vehículo">
                        <?php else: ?>
                            <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(0,0,0,0.2);">
                                <span style="font-size: 2.5rem;"></span>
                                <p style="color: #64748b; font-size: 0.9rem;">Imagen no disponible</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 5px;">
                        <div class="stats-badge">
                             <span style="color: #fbbf24; font-weight: bold;"><?php echo $c['nota_media'] ? round($c['nota_media'], 1) : '---'; ?></span> 
                            <span style="margin: 0 5px; opacity: 0.3;">|</span>
                             <?php echo $c['total_comentarios']; ?>
                        </div>
                    </div>

                    <?php if($ya_opino): ?>
                        <a href="comments.php?vehicle_id=<?php echo $c['id']; ?>#leer" class="btn-gris">
                            Ver opiniones (Participado)
                        </a>
                    <?php else: ?>
                        <a href="comments.php?vehicle_id=<?php echo $c['id']; ?>" class="btn-azul">
                            Opinar y ver detalles
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="vehicle-card" style="text-align: center; padding: 60px;">
                <p style="color: #9ca3af;">Aún no hay publicaciones.</p>
                <a href="add_vehicle.php" style="color: #3b82f6; text-decoration: none; font-weight: bold;">¡Sube la primera!</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>