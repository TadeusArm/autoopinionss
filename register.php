<?php
session_start();
include 'config/db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Generamos el token aunque no lo usemos ahora, por si tu tabla lo requiere
    $token = bin2hex(random_bytes(16));

    // 1. Comprobamos si el usuario o email ya existen
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->execute([$email, $username]);
    
    if ($check->fetch()) {
        $message = "Error: El nombre de usuario o el correo ya están registrados";
    } else {
        try {
            // 2. Insertamos con verified = 1 para que puedas testear el Login directamente
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, token, verified) VALUES (?, ?, ?, 'user', ?, 1)");
            
            if ($stmt->execute([$username, $email, $password, $token])) {
                $message = "¡Cuenta creada con éxito! Ya puedes iniciar sesión.";
            }
        } catch (PDOException $e) {
            $message = "Error: No se pudo crear la cuenta";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - AutoOpinions</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="overlay"></div>
    <div class="card">
        <h2 class="text-center">REGISTRO</h2>
        <p class="subtitle text-center">Únete a nuestra comunidad del motor</p>

        <?php if($message): ?>
            <?php $class = (strpos($message, 'Error') !== false) ? 'alert-error' : 'alert-success'; ?>
            <div class="alert <?php echo $class; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if(strpos($message, 'éxito') === false): ?>
        <form method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Nombre de usuario" required>
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn-submit">Registrarme</button>
        </form>
        <?php endif; ?>
        
        <p class="text-footer">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
        </p>
    </div>
</body>
</html>