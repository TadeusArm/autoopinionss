<?php
include 'config/db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username,email,password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);
        $message = "¡Cuenta creada con éxito!";
    } catch (PDOException $e) {
        $message = "Error: El correo ya está registrado";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - AutoOpinions</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="overlay"></div>
    <div class="card">
        <h2><i class="fa-solid fa-user-plus"></i> Registro</h2>
        <p class="subtitle">Únete a nuestra comunidad del motor</p>

        <?php if($message): ?>
            <?php $class = (strpos($message, 'Error') !== false) ? 'alert-error' : 'alert-success'; ?>
            <div class="alert <?php echo $class; ?>">
                <i class="fa-solid <?php echo ($class == 'alert-error') ? 'fa-circle-xmark' : 'fa-circle-check'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-wrapper">
                <i class="fa fa-user"></i>
                <input type="text" name="username" placeholder="Nombre de usuario" required>
            </div>
            <div class="input-wrapper">
                <i class="fa fa-envelope"></i>
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>
            <div class="input-wrapper">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit">Registrarme <i class="fa-solid fa-id-card"></i></button>
        </form>
        <p class="text-center" style="margin-top: 1.5rem;">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
        </p>
    </div>
</body>
</html>