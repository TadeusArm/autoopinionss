<header class="nav-header">
    <div class="header-container">
        <div class="nav-left">
            <a href="index.php" class="nav-logo">
                <img src="assets/img/logo.png" alt="Auto Opinions" class="header-logo-img">
            </a>
        </div>

        <nav class="nav-links">
            <a href="index.php" class="nav-explorar <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Explorar</a>
            
            <a href="following_feed.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'following_feed.php' ? 'active' : ''; ?>">Siguiendo</a>
            
            <a href="add_vehicle.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_vehicle.php' ? 'active' : ''; ?>">Publicar</a>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="nav-admin-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                    Admin
                </a>
            <?php endif; ?>
        </nav>

        <div class="nav-right">
            <a href="profile.php?id=<?php echo $_SESSION['user_id']; ?>" class="profile-pill" title="Mi Perfil">
                <span class="user-pill-name"><?php echo $_SESSION['username'] ?? 'Usuario'; ?></span>
                
                <div class="user-avatar-small">
                    <?php if(isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])): ?>
                        <img src="<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    </div>
</header>

<style>
    /* --- ESTILOS GENERALES (PC) --- */
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .header-logo-img {
        height: 50px;
        width: auto;
        display: block;
        transition: transform 0.3s ease;
    }

    .nav-links {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .nav-links a {
        text-decoration: none;
        color: #94a3b8;
        transition: 0.3s;
        font-weight: 500;
    }

    .nav-links a.active {
        color: #3b82f6;
        font-weight: bold;
    }

    .nav-admin-link {
        color: #fbbf24 !important;
        border: 1px solid rgba(251, 191, 36, 0.3);
        padding: 5px 12px;
        border-radius: 8px;
        background: rgba(251, 191, 36, 0.1);
    }

    /* --- AJUSTES EXCLUSIVOS PARA MÓVIL --- */
@media (max-width: 600px) {
    /* 1. Ajustamos el contenedor para que no apriete los lados */
    .header-container {
        padding: 0 5px !important;
        justify-content: space-between !important;
    }

    /* 2. Logo pequeño para que no estorbe */
    .nav-left { flex: 0 0 40px !important; }
    .header-logo-img { height: 20px !important; width: auto; }

    /* 3. OCULTAMOS EL NOMBRE (Imprescindible para que quepan los 3 botones) */
    .user-pill-name { display: none !important; }

    /* 4. REDISEÑO DEL PANEL DE BOTONES (nav-links) */
    .nav-links {
        display: flex !important;
        flex: 1 !important; /* Coge todo el sitio del centro */
        justify-content: center !important;
        gap: 4px !important; /* Espacio mínimo entre botones */
        margin: 0 5px !important;
        padding: 0 !important;
    }

    /* 5. FORZAMOS EL TAMAÑO DE LOS BOTONES */
    .nav-links a {
        display: block !important;
        flex: 1 !important; /* Todos miden lo mismo */
        font-size: 9px !important; /* Texto pequeño para que quepa todo */
        padding: 8px 2px !important; /* Más alto que ancho */
        text-align: center !important;
        background: #3b82f6 !important; /* Azul para todos */
        color: white !important;
        border-radius: 6px !important;
        text-decoration: none !important;
        font-weight: bold !important;
        min-width: 0 !important; /* Permite que se encojan */
        white-space: nowrap !important;
    }

    /* El de Admin en su color para diferenciar */
    .nav-admin-link {
        background: #fbbf24 !important;
        color: #000 !important;
    }

    /* Ocultamos Explorar para que quepan los importantes */
    .nav-links a[href="index.php"] {
        display: none !important;
    }

    /* 6. PERFIL (Derecha) */
    .nav-right { flex: 0 0 35px !important; }
    .user-avatar-small { width: 28px !important; height: 28px !important; margin: 0 !important; }
}
</style>