<?php
session_start();
include 'config/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Foto perfil
$stmt = $pdo->prepare("SELECT image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_image = $stmt->fetchColumn();

// Vehículos con usuario, comentarios y valoraciones
$stmt2 = $pdo->query("
    SELECT v.*, u.username,
    (SELECT COUNT(*) FROM comments c WHERE c.vehicle_id = v.id) AS comment_count,
    (SELECT AVG(rating) FROM ratings r WHERE r.vehicle_id = v.id) AS avg_rating
    FROM vehicles v
    JOIN users u ON v.user_id = u.id
    ORDER BY v.created_at DESC
");
$vehicles = $stmt2->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AutoOpinions - Foro de coches</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <h1>AutoOpinions 🚗</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="add_vehicle.php">Agregar vehículo</a>
        <a href="profile.php">Mi perfil</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>
</header>

<div class="profile-container">
    <img src="<?php echo $user_image ? $user_image : 'assets/img/default_profile.png'; ?>" alt="Perfil" onclick="toggleDropdown()">
    <div class="profile-dropdown" id="dropdown">
        <a href="profile.php">Ver perfil</a>
        <a href="profile_edit.php">Editar perfil</a>
        <a href="add_vehicle.php">Agregar vehículo</a>
        <a href="logout.php">Cerrar sesión</a>
    </div>
</div>

<div class="feed">
    <?php foreach($vehicles as $v): ?>
        <div class="vehicle-card">
            <h3><?php echo htmlspecialchars($v['brand'] . ' ' . $v['model']); ?> (<?php echo $v['year']; ?>)</h3>
            <p>Publicado por: <strong><?php echo htmlspecialchars($v['username']); ?></strong> | Fecha: <?php echo $v['created_at']; ?></p>
            <p><?php echo htmlspecialchars($v['description']); ?></p>
            <?php if($v['image']): ?>
                <img src="<?php echo $v['image']; ?>" alt="Imagen vehículo">
            <?php endif; ?>
            <p>⭐ Valoración: <?php echo $v['avg_rating'] ? number_format($v['avg_rating'],1) : 'Sin valoraciones'; ?> | 💬 Comentarios: <?php echo $v['comment_count']; ?></p>
            <p><a href="comments.php?vehicle_id=<?php echo $v['id']; ?>">Ver / Añadir comentarios</a></p>
        </div>
    <?php endforeach; ?>
</div>

<script>
function toggleDropdown(){
    const dropdown = document.getElementById('dropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}
window.onclick = function(event){
    if(!event.target.closest('.profile-container')){
        document.getElementById('dropdown').style.display = 'none';
    }
}
</script>

</body>
</html> 