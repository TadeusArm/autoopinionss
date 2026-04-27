<?php
include 'config/db.php';
$message = "";
$type = "error"; // Por defecto, el mensaje es de error hasta que se demuestre lo contrario

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // 1. Buscamos si existe un usuario con ese token
    $stmt = $pdo->prepare("SELECT id FROM users WHERE token = ? AND verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Si existe, activamos la cuenta y borramos el token para que no se use dos veces
        $update = $pdo->prepare("UPDATE users SET verified = 1, token = NULL WHERE id = ?");
        if ($update->execute([$user['id']])) {
            $message = "¡Cuenta activada con éxito! Ya puedes acceder a la comunidad.";
            $type = "success";
        } else {
            $message = "Hubo un error técnico al activar tu cuenta.";
        }
    } else {
        // El token no existe o la cuenta ya estaba verificada
        $message = "El enlace no es válido o ya ha sido utilizado.";
    }
} else {
    $message = "No se ha proporcionado ningún código de verificación.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación - AutoOpinions</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="overlay"></div>
    <div class="card text-center">
        <h2 style="margin-bottom: 20px;">VERIFICACIÓN</h2>
        
        <div class="alert <?php echo ($type == 'success') ? 'alert-success' : 'alert-error'; ?>" 
             style="<?php echo ($type == 'success') ? 'background:rgba(16,185,129,0.2); color:#4ade80; padding:15px; border-radius:12px;' : ''; ?>">
            <?php echo $message; ?>
        </div>

        <br>
        <div style="margin-top: 20px;">
            <a href="login.php" class="btn-submit" style="text-decoration: none; display: inline-block;">
                Ir al Inicio de Sesión
            </a>
        </div>
    </div>
</body>
</html>