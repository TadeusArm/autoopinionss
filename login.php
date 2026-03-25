<?php
session_start();
include 'config/db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $message = "Email o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - AutoOpinions</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="overlay"></div>
    <div class="card">
        <h2><i class="fa-solid fa-car-side"></i> AutoOpinions</h2>
        <p class="subtitle">Inicia sesión para continuar</p>

        <?php if($message): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-wrapper">
                <i class="fa fa-envelope"></i>
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>
            <div class="input-wrapper">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit">Entrar <i class="fa-solid fa-arrow-right-to-bracket"></i></button>
        </form>
        <p class="text-center" style="margin-top: 1.5rem;">
            ¿Eres nuevo? <a href="register.php">Crea una cuenta</a>
        </p>
    </div>
</body>
</html>