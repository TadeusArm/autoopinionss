<?php
session_start();
include 'config/db.php';

// SEGURIDAD: Solo el admin entra
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// LÓGICA PARA ELIMINAR (Baneo con efecto Cascada)
if (isset($_GET['delete_user'])) {
    $id_a_borrar = $_GET['delete_user'];
    if ($id_a_borrar != $_SESSION['user_id']) {
        $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $del->execute([$id_a_borrar]);
        header("Location: admin.php?msg=Usuario eliminado permanentemente");
        exit;
    }
}

$stmt = $pdo->query("SELECT id, username, email, role, profile_pic FROM users ORDER BY role ASC");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - AutoOpinions</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/img/favicon.jpg">
    <style>
        body {
            background: url('assets/img/admin.webp') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            color: white;
        }

        .admin-overlay {
            min-height: 100vh;
            background: rgba(0, 0, 0, 0.6); 
            padding: 40px 20px;
        }

        .glass-panel {
            max-width: 1100px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: none;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .admin-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .admin-header h1 {
            font-size: 2.5rem;
            margin: 0;
            background: linear-gradient(to right, #ffffff, #9ca3af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .table-container { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: #9ca3af;
            padding: 15px;
            text-align: left;
        }

        tr.user-row {
            background: rgba(255, 255, 255, 0.03);
            transition: all 0.3s ease;
        }

        tr.user-row:hover { background: rgba(255, 255, 255, 0.08); transform: translateY(-2px); }

        td { padding: 15px; border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05); }

        td:first-child { border-left: 1px solid rgba(255,255,255,0.05); border-radius: 12px 0 0 12px; }
        td:last-child { border-right: 1px solid rgba(255,255,255,0.05); border-radius: 0 12px 12px 0; }

        .user-info { display: flex; align-items: center; gap: 12px; }
        
        /* Ajuste de la foto clickable */
        .mini-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            object-fit: cover; border: 2px solid rgba(255,255,255,0.2);
            transition: transform 0.2s, border-color 0.2s;
        }

        .mini-avatar:hover {
            transform: scale(1.1);
            border-color: #3b82f6;
            cursor: pointer;
        }

        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: bold; }
        .badge-admin { background: rgba(251, 191, 36, 0.2); color: #fbbf24; border: 1px solid #fbbf24; }
        .badge-user { background: rgba(255, 255, 255, 0.1); color: #e5e7eb; }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            border: 1px solid rgba(239, 68, 68, 0.2);
            transition: 0.3s;
        }

        .btn-delete:hover { background: #ef4444; color: white; box-shadow: 0 0 15px rgba(239, 68, 68, 0.4); }

        .msg-alert {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid #10b981;
            color: #34d399;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="admin-overlay">
    <div class="glass-panel">
        
        <div class="admin-header">
            <h1>Panel de Gestión de Usuarios</h1>
            <p style="color: #9ca3af; margin-top: 10px;">Administra los privilegios y la comunidad de AutoOpinions</p>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="msg-alert"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rango</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $u): ?>
                    <tr class="user-row">
                        <td>
                            <div class="user-info">
                                <!-- ENLACE AL PERFIL EN LA FOTO -->
                                <a href="profile.php?id=<?php echo $u['id']; ?>" title="Ver perfil de <?php echo $u['username']; ?>">
                                    <?php if($u['profile_pic']): ?>
                                        <img src="<?php echo $u['profile_pic']; ?>" class="mini-avatar">
                                    <?php else: ?>
                                        <div class="mini-avatar" style="background:#4b5563; display:grid; place-items:center; font-size:12px;">
                                            <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <span><?php echo htmlspecialchars($u['username']); ?></span>
                            </div>
                        </td>
                        <td style="color: #9ca3af; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($u['email']); ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $u['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                <?php echo strtoupper($u['role']); ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                                <a href="admin.php?delete_user=<?php echo $u['id']; ?>" 
                                   class="btn-delete"
                                   onclick="return confirm('¿Baneamos a este usuario? Se eliminarán todos sus coches y comentarios.')">
                                    Bannear
                                </a>
                            <?php else: ?>
                                <span style="font-size: 0.8rem; color: #6b7280; font-style: italic;">Cuenta actual</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 40px; text-align: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px;">
            <a href="admin_dashboard.php" style="
                display: inline-flex;
                align-items: center;
                gap: 10px;
                background: rgba(59, 130, 246, 0.15);
                color: #3b82f6;
                padding: 12px 30px;
                border-radius: 12px;
                text-decoration: none;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 1px;
                font-size: 0.8rem;
                border: none;
                transition: 0.3s;
            " onmouseover="this.style.background='#3b82f6'; this.style.color='white';" 
               onmouseout="this.style.background='rgba(59, 130, 246, 0.15)'; this.style.color='#3b82f6';">
                <span style="font-size: 1.2rem;"></span> Ver Análisis del Sistema
            </a>
        </div>
    </div>
</div>

</body>
</html>