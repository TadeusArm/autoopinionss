<?php
session_start();
include 'config/db.php';

// Carga Manual Directa entrando en la carpeta src
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Ajustamos la ruta incluyendo el /src/
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(16));

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->execute([$email, $username]);
    
    if ($check->fetch()) {
        $message = "Error: El nombre de usuario o el correo ya están registrados";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, token, verified) VALUES (?, ?, ?, 'user', ?, 0)");
            
            if ($stmt->execute([$username, $email, $password, $token])) {
                
                // Asegúrate de que la carpeta en XAMPP se llame 'autoopinions'
                $enlace = "http://localhost/autoopinions/verificar.php?token=" . $token;
                
                if (enviarConfirmacionRegistro($email, $username, $enlace)) {
                    $message = "¡Cuenta creada! Por favor, revisa tu correo para activarla.";
                } else {
                    $message = "Error: No se pudo enviar el correo de activación.";
                }
            }
        } catch (PDOException $e) {
            $message = "Error: No se pudo crear la cuenta";
        }
    }
}

function enviarConfirmacionRegistro($emailDestino, $nombreDestino, $enlace) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'autoopinionss@gmail.com'; 
        $mail->Password   = 'zjdpcbqcskkdbxoz';        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('autoopinionss@gmail.com', 'AutoOpinions');
        $mail->addAddress($emailDestino, $nombreDestino);

        $mail->isHTML(true);
        $mail->Subject = 'Confirma tu registro en AutoOpinions';
        $mail->Body = "
            <div style='font-family: Arial; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #3b82f6;'>¡Casi listo!</h2>
                <p>Hola <b>$nombreDestino</b>, haz clic en el botón para verificar tu cuenta:</p>
                <a href='$enlace' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Activar mi cuenta</a>
            </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
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
            <div class="alert <?php echo (strpos($message, 'Error') !== false) ? 'alert-error' : 'alert-success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if(strpos($message, 'revisa tu correo') === false): ?>
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