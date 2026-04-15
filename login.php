<?php
session_start();
include 'config/db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Seleccionamos también la columna profile_pic
    $stmt = $pdo->prepare("SELECT id, username, password, profile_pic FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Guardamos la foto en la sesión al entrar
        $_SESSION['profile_pic'] = $user['profile_pic']; 
        
        header("Location: index.php");
        exit;
    } else {
        $message = "Email o contraseña incorrectos";
    }
}
?>
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AutoOpinions</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="overlay"></div>
    <div class="card">
        <h2 class="text-center">AUTO OPINIONS</h2>
        <p class="subtitle text-center">Inicia sesión para continuar</p>

        <?php if($message): ?>
            <div class="alert alert-error">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn-submit">Entrar</button>
        </form>
        
        <p class="text-footer">
            ¿Eres nuevo? <a href="register.php">Crea una cuenta</a>
        </p>
    </div>
</body>
</html>