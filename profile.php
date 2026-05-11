<?php
session_start();
include 'config/db.php';

// 1. Usamos el nombre que ya tenías: $perfil_id
$perfil_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Si no hay ID, al index
if ($perfil_id <= 0) {
    header("Location: index.php");
    exit;
}

// 2. Buscamos al usuario y lo guardamos en $usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$perfil_id]);
$usuario = $stmt->fetch();

// 3. Si no existe
if (!$usuario) {
    header("Location: user_banned.php");
    exit;
}

// 4. Definimos $mi_id para las comprobaciones
$mi_id = $_SESSION['user_id'] ?? 0;

// Contar Seguidores
$stmt_followers = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE followed_id = ?");
$stmt_followers->execute([$perfil_id]);
$total_seguidores = $stmt_followers->fetchColumn();

// Contar Seguidos
$stmt_following = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
$stmt_following->execute([$perfil_id]);
$total_seguidos = $stmt_following->fetchColumn();

// Verificar si YO sigo a este usuario
$lo_sigo = false;
if ($mi_id != $perfil_id) {
    $stmt_check = $pdo->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt_check->execute([$mi_id, $perfil_id]);
    $lo_sigo = (bool)$stmt_check->fetch();
}

// Obtener los coches publicados
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
            background: url('assets/img/fondoperfiles.jpg') center/cover no-repeat fixed !important; 
            color: white; 
            margin: 0;
            padding: 0;
            min-height: 100vh;
            position: relative;
        }

        /* CAPA DE GLASSMORPHISM SOBRE EL FONDO */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3); /* Oscurece un poco la foto */
            backdrop-filter: blur(10px);    /* Aplica el desenfoque cristalino */
            -webkit-backdrop-filter: blur(20px);
            z-index: 1;                     /* Detrás del contenido, delante de la foto */
        }

        .perfil-wrapper {
            max-width: 800px;
            margin: 110px auto 50px;
            padding: 0 20px;
            position: relative;
            z-index: 2; /* Por encima del efecto blur */
        }

        .card-perfil-header {
            background: rgba(17, 24, 39, 0.8);
            backdrop-filter: blur(5px);
            padding: 40px;
            border-radius: 24px;
            text-align: center;
            margin-bottom: 40px;
            border: none;
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
            margin: 0 auto 20px;
            font-size: 3rem;
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
        }

        .btn-follow {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 25px; 
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        .follow-active { background: #3b82f6; color: white; }
        .unfollow-active { background: rgba(255,255,255,0.1); color: white; }

        .bio-text {
            margin-top: 25px; 
            color: #cbd5e1;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .stat-num { display: block; font-size: 1.3rem; font-weight: bold; }
        .stat-label { font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; }

        .grid-publicaciones {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .mini-card-coche {
            background: rgba(31, 41, 55, 0.8);
            border-radius: 18px;
            overflow: hidden;
            text-decoration: none;
            color: white;
            transition: 0.3s;
            border: none;
        }

        .mini-card-coche:hover { transform: translateY(-5px); background: rgba(31, 41, 55, 0.9); }

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
                $foto_user = $usuario['profile_pic'] ?? '';
                if(!empty($foto_user) && file_exists($foto_user)): ?>
                    <img src="<?php echo htmlspecialchars($foto_user); ?>" alt="Avatar">
                <?php else: ?>
                    <?php echo strtoupper(substr($usuario['username'] ?? 'U', 0, 1)); ?>
                <?php endif; ?>
            </div>

            <h1 class="username-title">@<?php echo htmlspecialchars($usuario['username']); ?></h1>

            <?php if($mi_id != $perfil_id): ?>
                <form action="acciones/follow.php" method="POST" style="display:inline;">
                    <input type="hidden" name="followed_id" value="<?php echo $perfil_id; ?>">
                    <?php if($lo_sigo): ?>
                        <button type="submit" name="accion" value="unfollow" class="btn-follow unfollow-active">Siguiendo</button>
                    <?php else: ?>
                        <button type="submit" name="accion" value="follow" class="btn-follow follow-active">Seguir</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
            
            <div class="bio-text">
                <?php echo $usuario['bio'] ? nl2br(htmlspecialchars($usuario['bio'])) : "Sin biografía definida."; ?>
            </div>

            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-num"><?php echo count($coches_usuario); ?></span>
                    <span class="stat-label">Coches</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num"><?php echo $total_seguidores; ?></span>
                    <span class="stat-label">Seguidores</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num"><?php echo $total_seguidos; ?></span>
                    <span class="stat-label">Siguiendo</span>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 25px; font-size: 1.2rem; font-weight: 700;">
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
                                <img src="<?php echo htmlspecialchars($ruta_coche); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#64748b;">Sin foto</div>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 15px;">
                            <h4 style="margin:0; font-size: 1.1rem;"><?php echo htmlspecialchars($v['brand'] . " " . $v['model']); ?></h4>
                            <div style="margin-top: 8px; font-size: 0.85rem; color: #94a3b8; display: flex; justify-content: space-between;">
                                <span>Nota: <?php echo $v['nota_media'] ? round($v['nota_media'], 1) : '--'; ?></span>
                                <span><?php echo $v['total_comentarios']; ?> opiniones</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: rgba(255,255,255,0.05); border-radius: 15px;">
                    Este usuario aún no ha subido ningún coche.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>