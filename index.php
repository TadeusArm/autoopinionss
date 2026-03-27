<?php
session_start();
include 'config/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Sacar los coches con nota y número de comentarios
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
    <title>AutoOpinions - Inicio</title>
    <style>
        /* Estilos directos para que no se vea negro */
        body { background-color: #121212; color: white; font-family: 'Segoe UI', sans-serif; margin: 0; }
        .nav { background: #1f1f1f; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #333; }
        .nav a { color: #3b82f6; text-decoration: none; font-weight: bold; margin-left: 20px; }
        .contenedor { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .tarjeta { background: #1e1e1e; border-radius: 12px; border: 1px solid #333; padding: 20px; margin-bottom: 25px; }
        .img-coche { width: 100%; height: 350px; object-fit: cover; border-radius: 8px; margin: 15px 0; border: 1px solid #333; }
        .btn { display: block; text-align: center; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 15px; }
        .btn-azul { background: #2563eb; color: white; }
        .btn-gris { background: #4b5563; color: white; }
        .info-user { color: #9ca3af; font-size: 0.9rem; }
    </style>
</head>
<body>
    <header class="nav">
        <div style="font-size: 1.5rem; color: #2563eb; font-weight: 800;">AUTO OPINIONS</div>
        <div>
            <a href="index.php">Inicio</a>
            <a href="add_vehicle.php">Subir Coche</a>
            <a href="logout.php" style="color: #ef4444;">Salir</a>
        </div>
    </header>

    <div class="contenedor">
        <h2 style="border-left: 5px solid #2563eb; padding-left: 15px;">Muro de la Comunidad</h2>

        <?php foreach($coches as $c): ?>
            <?php 
                // Comprobamos si el usuario ya ha opinado en este coche
                $st = $pdo->prepare("SELECT id FROM comments WHERE user_id = ? AND vehicle_id = ?");
                $st->execute([$user_id, $c['id']]);
                $ya_opino = $st->fetch();
            ?>
            
            <div class="tarjeta">
                <span class="info-user">Publicado por <strong>@<?php echo $c['username']; ?></strong></span>
                <h3 style="margin: 10px 0; font-size: 1.6rem;"><?php echo $c['brand'] . " " . $c['model']; ?> (<?php echo $c['year']; ?>)</h3>
                
                <?php if($c['image']): ?>
                    <img src="<?php echo $c['image']; ?>" class="img-coche">
                <?php endif; ?>

                <div style="background: #2a2a2a; padding: 10px; border-radius: 6px; display: inline-block;">
                     Nota: <span style="color: #fbbf24; font-weight: bold;"><?php echo $c['nota_media'] ? round($c['nota_media'], 1) : '---'; ?></span> 
                    |  <?php echo $c['total_comentarios']; ?> comentarios
                </div>

                <?php if($ya_opino): ?>
                    <a href="comments.php?vehicle_id=<?php echo $c['id']; ?>#leer" class="btn btn-gris">
                        Ver opiniones (Ya has opinado aquí)
                    </a>
                <?php else: ?>
                    <a href="comments.php?vehicle_id=<?php echo $c['id']; ?>" class="btn btn-azul">
                        Opinar y ver detalles
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>