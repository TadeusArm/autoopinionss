<header class="nav-header">
    <div class="header-container">
        <div class="nav-left">
            <a href="index.php" class="nav-logo">AUTO OPINIONS</a>
        </div>

        <nav class="nav-links">
            <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Explorar</a>
            <a href="add_vehicle.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_vehicle.php' ? 'active' : ''; ?>">Publicar</a>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="nav-admin-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>" style="color: #fbbf24; font-weight: bold; border: 1px solid rgba(251, 191, 36, 0.3); padding: 5px 12px; border-radius: 8px; background: rgba(251, 191, 36, 0.1);">
                     Admin
                </a>
            <?php endif; ?>
        </nav>

        <div class="nav-right">
            <a href="edit_profile.php" class="profile-pill" title="Mi Perfil">
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
    /* Estilo extra para que el botón de admin brille un poco al pasar el ratón */
    .nav-admin-link:hover {
        background: rgba(251, 191, 36, 0.2) !important;
        box-shadow: 0 0 10px rgba(251, 191, 36, 0.2);
        color: white !important;
    }
</style>