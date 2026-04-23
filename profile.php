<?php
session_start();
include 'config/db.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])){
    header("Location: index.php");
    exit;
}

$perfil_id = $_GET['id'];
$mi_id = $_SESSION['user_id'];

// 1. Obtener datos del dueño del perfil
$stmt = $pdo->prepare("SELECT username, bio, location, instagram_user, profile_pic FROM users WHERE id = ?");
$stmt->execute([$perfil_id]);
$usuario = $stmt->fetch();

if (!$usuario) { 
    echo "Usuario no encontrado."; 
    exit; 
}

// 2. Obtener los coches publicados por este usuario
$stmt_coches = $pdo->prepare("
    SELECT v.*, 
    (SELECT COUNT(*) FROM comments WHERE vehicle_id = v.id) as total_comentarios,
    (SELECT AVG(rating) FROM ratings WHERE vehicle_id = v.id) as nota_media
    FROM vehicles v 
    WHERE v.user_id = ? 
    ORDER BY v.id DESC
");
$stmt_coches->execute([$perfil_id]);
$coches_usuario = $stmt_coches->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de @<?php echo htmlspecialchars($usuario['username']); ?> - AutoOpinions</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { 
            background: url('assets/img/fondo-index.jpg') center/cover no-repeat fixed !important; 
            color: white; 
            margin: 0;
            padding: 0;
        }

        .perfil-wrapper {
            max-width: 800px;
            margin: 110px auto 50px;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        .card-perfil-header {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 24px;
            border: 0px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .user-avatar-big {
            width: 120px;
            height: 120px;
            background: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-weight: bold;
            color: white;
            margin: 0 auto 20px;
            font-size: 3rem;
            border: 0px solid rgba(59, 130, 246, 0.3);
        }

        .user-avatar-big img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .username-title {
            font-size: 2.2rem;
            color: #3b82f6;
            margin: 0;
            letter-spacing: -1px;
        }

        .location-text {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .bio-text {
            color: #d1d5db;
            line-height: 1.7;
            margin: 20px auto;
            max-width: 600px;
            font-size: 1.05rem;
        }

        .social-link {
            display: inline-block;
            background: rgba(225, 48, 108, 0.15);
            color: #fb7185;
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.85rem;
            border: 0px solid rgba(225, 48, 108, 0.2);
            transition: 0.3s;
        }

        .grid-publicaciones {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .mini-card-coche {
            background: rgba(31, 41, 55, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 18px;
            overflow: hidden;
            border: 0px solid rgba(255, 255, 255, 0.05);
            transition: 0.3s;
            text-decoration: none;
            color: white;
        }

        .mini-card-coche:hover {
            transform: translateY(-5px);
            border-color: rgba(59, 130, 246, 0.5);
        }

        .btn-editar-perfil {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.8rem;
            border: 0px solid rgba(255,255,255,0.1);
        }

        .mini-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        @media (max-width: 768px) {
            .perfil-wrapper { margin-top: 30px; }
            .card-perfil-header { padding: 25px; border-radius: 0; margin-left: -20px; margin-right: -20px; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="perfil-wrapper">
        
        <div class="card-perfil-header">
            <?php if($perfil_id == $mi_id): ?>
                <a href="edit_profile.php" class="btn-editar-perfil">Configuración</a>
            <?php endif; ?>

            <div class="user-avatar-big">
                <?php 
                    // Ajuste: Usao la ruta almacenada directamente (assets/img/avatars/...)
                    $foto_user = $usuario['profile_pic'] ?? '';

                    if(!empty($foto_user) && file_exists($foto_user)): ?>
                        <img src="<?php echo htmlspecialchars($foto_user); ?>" alt="Avatar">
                    <?php else: ?>
                        <?php echo strtoupper(substr($usuario['username'] ?? 'U', 0, 1)); ?>
                    <?php endif; ?>
            </div>

            <h1 class="username-title">@<?php echo htmlspecialchars($usuario['username']); ?></h1>
            
            <?php if(!empty($usuario['location'])): ?>
                <div class="location-text">
                    Ubicación: <?php echo htmlspecialchars($usuario['location']); ?>
                </div>
            <?php endif; ?>

            <div class="bio-text">
                <?php echo $usuario['bio'] ? nl2br(htmlspecialchars($usuario['bio'])) : "Sin biografía definida."; ?>
            </div>

            <?php if(!empty($usuario['instagram_user'])): ?>
                <a href="https://instagram.com/<?php echo htmlspecialchars($usuario['instagram_user']); ?>" target="_blank" class="social-link">
                    Instagram
                </a>
            <?php endif; ?>

            <div style="margin-top: 30px; display: flex; justify-content: center; gap: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                <div>
                    <span style="display: block; font-size: 1.2rem; font-weight: bold; color: #3b82f6;"><?php echo count($coches_usuario); ?></span>
                    <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Publicaciones</span>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 25px; font-size: 1.2rem; font-weight: 700; color: white; padding-left: 0px; text-transform: none; letter-spacing: -0.5px;">
    Garaje de <?php echo htmlspecialchars($usuario['username']); ?>
</h3>

<div class="grid-publicaciones">
    <?php if(count($coches_usuario) > 0): ?>
        <?php foreach($coches_usuario as $v): ?>
            <?php 
                $img = $v['image'];
                $ruta_coche = "assets/img/vehicles/" . $img;
            ?>
            <a href="comments.php?vehicle_id=<?php echo $v['id']; ?>" class="mini-card-coche">
                <div style="width: 100%; height: 180px; background: #111;">
                    <?php if(!empty($img) && file_exists($ruta_coche)): ?>
                        <img src="<?php echo htmlspecialchars($ruta_coche); ?>" 
                             style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#64748b; font-size:0.8rem; background: rgba(0,0,0,0.2);">
                            Sin foto
                        </div>
                    <?php endif; ?>
                </div>
                <div style="padding: 15px;">
                    <h4 style="margin:0; font-size: 1.1rem;"><?php echo htmlspecialchars($v['brand'] . " " . $v['model']); ?></h4>
                    <div class="mini-stats" style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 0.8rem; color: #94a3b8;">
                        <span>Nota: <?php echo $v['nota_media'] ? round($v['nota_media'], 1) : '--'; ?></span>
                        <span><?php echo $v['total_comentarios']; ?> opiniones</span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: rgba(255,255,255,0.05); border-radius: 15px; color: #94a3b8;">
            Este usuario aún no ha subido ningún coche a su garaje.
        </div>
    <?php endif; ?>
</div>
    </div>
</body>
</html>