<?php
session_start();
include 'config/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$search = $_GET['search'] ?? '';
$brand_filter = $_GET['brand'] ?? '';
$min_year = $_GET['min_year'] ?? '';
$max_year = $_GET['max_year'] ?? '';
$min_km = $_GET['min_km'] ?? '';
$max_km = $_GET['max_km'] ?? '';

// 2. SQL DINÁMICO
$sql = "SELECT v.*, u.username, 
        (SELECT COUNT(*) FROM comments WHERE vehicle_id = v.id) as total_comentarios,
        (SELECT AVG(rating) FROM ratings WHERE vehicle_id = v.id) as nota_media
        FROM vehicles v 
        JOIN users u ON v.user_id = u.id 
        WHERE 1=1"; 

$params = [];
if (!empty($search)) { $sql .= " AND (v.brand LIKE ? OR v.model LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if (!empty($brand_filter)) { $sql .= " AND v.brand = ?"; $params[] = $brand_filter; }
if (!empty($min_year)) { $sql .= " AND v.year >= ?"; $params[] = (int)$min_year; }
if (!empty($max_year)) { $sql .= " AND v.year <= ?"; $params[] = (int)$max_year; }
if (!empty($min_km)) { $sql .= " AND v.km >= ?"; $params[] = (int)$min_km; }
if (!empty($max_km)) { $sql .= " AND v.km <= ?"; $params[] = (int)$max_km; }

$sql .= " ORDER BY v.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$coches = $stmt->fetchAll();

$marcas_query = $pdo->query("SELECT DISTINCT brand FROM vehicles WHERE brand != '' ORDER BY brand ASC");
$todas_las_marcas = $marcas_query->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AutoOpinions - Muro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* Fondo y reseteo */
        body { 
            background: url('assets/img/fondo-index.jpg') center/cover no-repeat fixed !important; 
            margin: 0;
            padding: 0;
        }

        /* HEADER FIX PARA EVITAR HUECOS BLANCOS */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        /* SIDEBAR IZQUIERDA */
        .sidebar-left {
            width: 300px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(15px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px 20px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0; 
            padding-top: 100px; 
            z-index: 900;
            box-sizing: border-box;
            overflow-y: auto;
        }

        .sidebar-left h4 {
            color: #3b82f6;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 2px;
            margin-bottom: 25px;
        }

        .filter-group { margin-bottom: 20px; }
        .filter-group label { display: block; color: #94a3b8; font-size: 0.75rem; margin-bottom: 8px; font-weight: bold; }
        
        .range-container { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .sidebar-left input, .sidebar-left select {
            width: 100%;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 10px;
            border-radius: 8px;
            outline: none;
        }

        .btn-filter {
            width: 100%;
            background: #3b82f6;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        /* CONTENEDOR DEL FEED */
        .feed-container {
    margin-left: 300px; 
    position: relative;
    z-index: 2;
    max-width: 650px; 
    margin-right: auto;
    margin-left: auto; 
    padding: 0 20px;
    padding-top: 30px !important; 
    margin-top: 10px !important; 
}

        /* ESTILOS DE CARDS ORIGINALES */
        .img-wrapper {
            width: 100%;
            height: 300px; 
            overflow: hidden;
            border-radius: 12px;
            margin: 12px 0;
            border: 0px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.3);
        }

        .img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .stats-badge {
            background: rgba(0, 0, 0, 0.5);
            padding: 8px 12px;
            border-radius: 8px;
            display: inline-block;
            border: 0px solid rgba(255, 255, 255, 0.1);
            font-size: 0.85rem;
            margin-bottom: 8px;
        }

        .btn-azul {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 12px;
            display: block;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            font-size: 0.95rem;
        }

        .btn-gris {
            background: rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            padding: 12px;
            display: block;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            border: 0px solid rgba(255, 255, 255, 0.1);
            font-size: 0.95rem;
        }

        .vehicle-card { margin-bottom: 40px; }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <?php include 'includes/header.php'; ?>

    <aside class="sidebar-left">
        <h4>Filtros Avanzados</h4>
        <form method="GET">
            <div class="filter-group">
                <label>Buscar</label>
                <input type="text" name="search" placeholder="Ej: LFA..." value="<?= htmlspecialchars($search) ?>">
            </div>

            <div class="filter-group">
                <label>Marca</label>
                <select name="brand">
                    <option value="">Todas</option>
                    <?php foreach($todas_las_marcas as $m): ?>
                        <option value="<?= $m ?>" <?= ($brand_filter == $m)?'selected':'' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Año (Min - Max)</label>
                <div class="range-container">
                    <select name="min_year">
                        <option value="">Mín</option>
                        <?php for($i=2026; $i>=1980; $i--): ?>
                            <option value="<?= $i ?>" <?= ($min_year == $i)?'selected':'' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="max_year">
                        <option value="">Máx</option>
                        <?php for($i=2026; $i>=1980; $i--): ?>
                            <option value="<?= $i ?>" <?= ($max_year == $i)?'selected':'' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="filter-group">
                <label>Kilómetros (Min - Max)</label>
                <div class="range-container">
                    <select name="min_km">
                        <option value="">Mín</option>
                        <?php for($i=0; $i<=150000; $i+=10000): ?>
                            <option value="<?= $i ?>" <?= ($min_km !== '' && $min_km == $i)?'selected':'' ?>><?= number_format($i, 0, '', '.') ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="max_km">
                        <option value="">Máx</option>
                        <?php for($i=10000; $i<=250000; $i+=10000): ?>
                            <option value="<?= $i ?>" <?= ($max_km == $i)?'selected':'' ?>><?= number_format($i, 0, '', '.') ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-filter">ACTUALIZAR</button>
            <a href="index.php" style="display:block; text-align:center; margin-top:15px; color:#64748b; font-size:0.75rem; text-decoration:none;">Limpiar filtros</a>
        </form>
    </aside>

    <div class="feed-container">
        <h2 style="border-left: 5px solid #3b82f6; padding-left: 15px; margin-bottom: 25px; text-shadow: 2px 2px 5px rgba(0,0,0,0.7); font-size: 1.6rem; color: #fff;">
            Muro de la Comunidad
        </h2>

        <?php if(count($coches) > 0): ?>
            <?php foreach($coches as $c): ?>
                <?php 
                    $st = $pdo->prepare("SELECT id FROM comments WHERE user_id = ? AND vehicle_id = ?");
                    $st->execute([$user_id, $c['id']]);
                    $ya_opino = $st->fetch();

                    $img_db = $c['image'];
                    $ruta_final = (strpos($img_db, 'assets/') === 0) ? $img_db : "assets/img/vehicles/" . $img_db;
                ?>
                
                <div class="vehicle-card">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <div>
                            <span style="color: #9ca3af; font-size: 0.8rem;">Publicado por</span>
                            <strong style="color: #3b82f6; display: block; font-size: 1rem;">@<?php echo htmlspecialchars($c['username']); ?></strong>
                        </div>
                        <span style="color: #6b7280; font-size: 0.9rem; font-weight: bold;"><?php echo htmlspecialchars($c['year']); ?></span>
                    </div>

                    <h3 style="margin: 10px 0; font-size: 1.6rem; letter-spacing: -0.5px; color: #f8fafc;">
                        <?php echo htmlspecialchars($c['brand'] . " " . $c['model']); ?>
                    </h3>
                    
                    <div class="img-wrapper">
                        <?php if(!empty($img_db)): ?>
                            <img src="<?php echo htmlspecialchars($ruta_final); ?>" alt="Vehículo">
                        <?php else: ?>
                            <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(0,0,0,0.2);">
                                <p style="color: #64748b; font-size: 0.9rem;">Imagen no disponible</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 5px;">
                        <div class="stats-badge" style="color:#fff;">
                             <span style="color: #fbbf24; font-weight: bold;"><?php echo $c['nota_media'] ? round($c['nota_media'], 1) : '---'; ?></span> 
                            <span style="margin: 0 5px; opacity: 0.3;">|</span>
                             <?php echo $c['total_comentarios']; ?> opiniones
                        </div>
                    </div>

                    <?php if($ya_opino): ?>
                        <a href="comments.php?vehicle_id=<?php echo $c['id']; ?>#leer" class="btn-gris">
                            Ver opiniones (Participado)
                        </a>
                    <?php else: ?>
                        <a href="comments.php?vehicle_id=<?php echo $c['id']; ?>" class="btn-azul">
                            Opinar y ver detalles
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #9ca3af; text-align: center; padding: 50px;">No hay coches con esos filtros.</p>
        <?php endif; ?>
    </div>

</body>
</html>