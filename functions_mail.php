<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargamos PHPMailer (ajusta la ruta si es necesario)
require 'vendor/autoload.php'; 

/**
 * Función única para envíos de email
 * @param string $emailDestino Correo del receptor
 * @param string $usernameDestino Nombre del receptor
 * @param string $tipo 'verify' para registros, 'comment' para avisos
 * @param string $extra Token de verificación o Nombre del coche
 */
function enviarNotificacionEmail($emailDestino, $usernameDestino, $tipo, $extra = '') {
    $mail = new PHPMailer(true);

    try {
        
        $mail->isSMTP();
        $mail->Host       = 'smtp de tu hosting';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tucorreo';
        $mail->Password   = 'TucontraseñaSegura'; // Cambia esto por tu contraseña real
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = tu puerto; // Por ejemplo, 587 para TLS
        $mail->CharSet    = 'UTF-8';
        

        
        $mail->setFrom('notificaciones@autoopinions.es', 'AutoOpinions');
        $mail->addAddress($emailDestino, $usernameDestino);

        $mail->isHTML(true);

        
        if ($tipo === 'verify') {
            // Email de Registro
            $enlace = "https://tuweb/verify.php?token=" . $extra;
            $mail->Subject = 'Confirma tu cuenta en AutoOpinions';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>¡Hola $usernameDestino!</h2>
                    <p>Gracias por unirte a nuestra comunidad. Para activar tu cuenta, haz clic en el botón:</p>
                    <a href='$enlace' style='background:#60a5fa; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block;'>Verificar mi cuenta</a>
                </div>";
        } 
        elseif ($tipo === 'comment') {
            // Email de Notificación de Comentario
            $mail->Subject = '¡Nueva opinión sobre tu coche!';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>¡Buenas noticias, $usernameDestino!</h2>
                    <p>Alguien ha dejado un nuevo comentario y una valoración en tu <strong>$extra</strong>.</p>
                    <p>Entra ya para ver qué opinan los demás usuarios.</p>
                    <br>
                    <a href='https://tuweb' style='color:#60a5fa;'>Ir a AutoOpinions</a>
                </div>";
        }

        $mail->send();
        return true;

    } catch (Exception $e) {
       
        return false;
    }
}