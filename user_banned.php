<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/favicon.jpg">
    <title>Alerta - AutoOpinions</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            width: 100%;
            overflow: hidden;
            background-color: #000; /* Fondo negro por si la imagen tarda en cargar */
            font-family: 'Helvetica', Arial, sans-serif;
        }

        /* La imagen de baneo ocupa toda la pantalla */
        .baneo-container {
            height: 100vh;
            width: 100vw;
            background: url('assets/img/BaneoFoto.png') no-repeat center center;
            background-size: contain; /* O 'cover' si quieres que no queden franjas negras */
            display: flex;
            position: relative;
        }

        /* Botón estilo GTA en la esquina inferior derecha */
        .btn-volver {
            position: absolute;
            bottom: 40px;
            right: 50px;
            background-color: rgba(43, 88, 133, 0.9); /* Azul oscuro tipo Rockstar */
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 18px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
        }

        .btn-volver:hover {
            background-color: rgba(59, 130, 246, 1);
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        /* El pequeño cuadrado que simula el icono de 'Enter' o 'Aceptar' */
        .btn-volver::after {
            content: '↵';
            display: inline-block;
            background: white;
            color: black;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 14px;
            margin-left: 10px;
        }
    </style>
</head>
<body>

    <div class="baneo-container">
        <a href="index.php" class="btn-volver">
            Volver al Feed
        </a>
    </div>

</body>
</html>