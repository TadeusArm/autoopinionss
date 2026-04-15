<?php
session_start();
include 'config/db.php';

// Protección de ruta
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$mensaje = "";
$error = "";

// Lógica de actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['username'];

    // 1. GESTIÓN DE LA FOTO DE PERFIL
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_pic']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = $user_id . "_" . time() . "." . $ext;
            $upload_path = "assets/img/avatars/" . $new_filename;

            if (!is_dir("assets/img/avatars/")) {
                mkdir("assets/img/avatars/", 0777, true);
            }

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                $sql_pic = "UPDATE users SET profile_pic = ? WHERE id = ?";
                $pdo->prepare($sql_pic)->execute([$upload_path, $user_id]);
                $_SESSION['profile_pic'] = $upload_path;
                $mensaje = "¡Foto de perfil actualizada!";
            }
        } else {
            $error = "Formato de imagen no válido.";
        }
    }

    // 2. GESTIÓN DEL NOMBRE DE USUARIO
    if (!empty($new_username)) {
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$new_username, $user_id])) {
            $_SESSION['username'] = $new_username;
            if(empty($mensaje)) $mensaje = "¡Datos guardados correctamente!";
        }
    }
}

// Consultar datos actuales
$stmt = $pdo->prepare("SELECT username, email, profile_pic FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - AutoOpinions</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .edit-container { max-width: 480px; margin: 50px auto; padding: 0 20px; }
        .profile-header { text-align: center; margin-bottom: 30px; }
        
        /* Avatar Picker */
        .avatar-picker { position: relative; width: 100px; height: 100px; margin: 0 auto 15px; }
        .profile-avatar-big {
            width: 100%; height: 100%; border-radius: 25px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-weight: 900; color: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            overflow: hidden; background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border: 1.5px solid rgba(255, 255, 255, 0.1);
        }
        .profile-avatar-big img { width: 100%; height: 100%; object-fit: cover; }
        .change-pic-btn {
            position: absolute; bottom: -5px; right: -5px;
            background: #1f2937; border: 1.5px solid #374151; color: #94a3b8;
            border-radius: 50%; width: 34px; height: 34px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: 0.3s; z-index: 10;
        }
        .change-pic-btn:hover { background: #3b82f6; color: white; transform: scale(1.1); }
        #file-input { display: none; }

        /* Botón Secundario para Contraseña */
        .btn-link-sec {
            display: block; text-align: center; padding: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 0px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px; color: #94a3b8;
            text-decoration: none; font-size: 0.9rem; font-weight: 600;
            margin-bottom: 25px; transition: 0.3s;
        }
        .btn-link-sec:hover { background: rgba(255, 255, 255, 0.08); color: white; }

        .alert { padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 0.9rem; }
        .alert-success { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
        .alert-error { background: rgba(239, 68, 68, 0.15); color: #fca5a5; }

        .logout-section { margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.08); text-align: center; }
        .btn-logout { color: #fca5a5; text-decoration: none; font-size: 0.9rem; font-weight: 600; opacity: 0.8; transition: 0.3s; }
        .btn-logout:hover { opacity: 1; color: #ef4444; }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <?php include 'includes/header.php'; ?>

    <div class="edit-container">
        <form method="POST" enctype="multipart/form-data" class="card">
            <div class="profile-header">
                <div class="avatar-picker">
                    <div class="profile-avatar-big" id="avatar-preview">
                        <?php if($user['profile_pic']): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Avatar">
                        <?php else: ?>
                            <span id="avatar-placeholder"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <label for="file-input" class="change-pic-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    </label>
                    <input id="file-input" type="file" name="profile_pic" accept="image/*">
                </div>
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p style="color: #64748b; font-size: 0.85rem;"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <?php if($mensaje): ?> <div class="alert alert-success"><?php echo $mensaje; ?></div> <?php endif; ?>
            <?php if($error): ?> <div class="alert alert-error"><?php echo $error; ?></div> <?php endif; ?>

            <div style="margin-bottom: 20px;">
                <label style="display:block; color:#94a3b8; font-size:0.85rem; margin-bottom:8px;">Nombre de usuario</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <a href="change_password.php" class="btn-link-sec">
                Configurar nueva contraseña
            </a>

            <button type="submit" class="btn-submit">Guardar cambios</button>

            <div class="logout-section">
                <a href="logout.php" class="btn-logout">Cerrar sesión</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('file-input').onchange = function(evt) {
            const files = evt.target.files;
            if (FileReader && files && files.length) {
                const fr = new FileReader();
                fr.onload = function () {
                    const preview = document.getElementById('avatar-preview');
                    const placeholder = document.getElementById('avatar-placeholder');
                    let img = preview.querySelector('img');
                    if (!img) { img = document.createElement('img'); preview.appendChild(img); }
                    img.src = fr.result;
                    if (placeholder) placeholder.style.display = 'none';
                }
                fr.readAsDataURL(files[0]);
            }
        };
    </script>
</body>
</html>