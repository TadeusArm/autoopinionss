<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

function enviarNotificacionEmail($emailDestino, $nombreDestino, $tipoNotificacion, $nombreVehiculo) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';        
        $mail->SMTPAuth   = true;
        $mail->Username   = 'autoopinionss@gmail.com';   
        $mail->Password   = 'zjdpcbqcskkdbxoz';      
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Destinatarios
        $mail->setFrom('AutoOpinions@gmail.com', 'AutoOpinions');
        $mail->addAddress($emailDestino, $nombreDestino);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = ($tipoNotificacion == 'comment') ? 'Nueva opinion en tu coche' : 'Nueva valoracion';
        
        // Diseño del mensaje
        $mensaje = ($tipoNotificacion == 'comment') 
            ? "Hola $nombreDestino, alguien ha dejado un comentario en tu <b>$nombreVehiculo</b>."
            : "Hola $nombreDestino, han puntuado tu <b>$nombreVehiculo</b>.";

        $mail->Body = "
        <div style='font-family: Arial; padding: 20px; border: 1px solid #ddd;'>
            <h2 style='color: #3b82f6;'>AutoOpinions</h2>
            <p>$mensaje</p>
            <br>
            <a href='http://tuweb.com/index.php' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver en la web</a>
        </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}