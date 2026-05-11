<?php
session_start();
include 'config/db.php';

// Carga Manual Directa entrando en la carpeta src
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

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
               
                
                
                $enlace = "https://autoopinions.wuaze.com//verificar.php?token=" . $token;
                
               
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

        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom('autoopinionss@gmail.com', 'AutoOpinions');
        $mail->addAddress($emailDestino, $nombreDestino);

        $mail->isHTML(true);
        $mail->Subject = 'Confirma tu registro en AutoOpinions';
        $mail->Body = "
            <div style='font-family: Arial; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #3b82f6;'>¡Casi listo!</h2>
                <p>Hola <b>$nombreDestino</b>, haz clic en el botón para verificar tu cuenta:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$enlace' style='background: #3b82f6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Activar mi cuenta</a>
                </div>
                <p style='font-size: 12px; color: #777;'>Si no puedes hacer clic, copia este enlace: $enlace</p>
            </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>