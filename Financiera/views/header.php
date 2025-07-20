<?php
// Verificar si la función existe y hay una conexión válida
if (function_exists('getUltimosPrestamos') && isset($conn) && isset($user_info)) {
    $ultimos_prestamos = getUltimosPrestamos($conn, $user_info);
    var_dump($ultimos_prestamos);
} else {
    $ultimos_prestamos = [];
}

// Inicializar el array de notificaciones
$notificaciones = [];

// Si hay préstamos, procesarlos
if (!empty($ultimos_prestamos)) {
    foreach ($ultimos_prestamos as $prestamo) {
        if (isset($prestamo['nombre_cliente']) && isset($prestamo['monto'])) {
            $notificaciones[] = [
                'nombre_cliente' => $prestamo['nombre_cliente'],
                'monto' => $prestamo['monto'],
                'estado_solicitud' => $prestamo['estado_solicitud']
            ];
        }
    }
}
?>

<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="index.php" class="logo d-flex align-items-center">
            <img src="assets/img/logo.png" alt="">
            <span class="d-none d-lg-block">Financiera</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>
    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
            <!-- Notifications -->
            <li class="nav-item dropdown">
                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    <?php if (!empty($notificaciones)): ?>
                        <span class="badge bg-primary badge-number"><?php echo count($notificaciones); ?></span>
                    <?php endif; ?>
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                    <?php if (!empty($notificaciones)): ?>
                        <li class="dropdown-header">
                            Tienes <?php echo count($notificaciones); ?> notificaciones nuevas
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <div class="notifications-scroll">
                            <?php foreach ($notificaciones as $notif): ?>
                                <li class="notification-item">
                                    <i class="bi bi-exclamation-circle text-warning"></i>
                                    <div>
                                        <h4><?php echo htmlspecialchars($notif['nombre_cliente']); ?></h4>
                                        <p>Monto: $<?php echo number_format($notif['monto'], 2); ?></p>
                                        <p>Estado: <?php echo htmlspecialchars($notif['estado_solicitud']); ?></p>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <li class="dropdown-header">
                            No hay notificaciones nuevas
                        </li>
                    <?php endif; ?>
                </ul>
            </li>

            <!-- Agregar estilos personalizados -->
            <style>
                .notifications {
                    min-width: 300px;
                    max-width: 100%;
                    max-height: 80vh;
                }

                .notifications-scroll {
                    max-height: 300px;
                    overflow-y: auto;
                }

                .notification-item {
                    display: flex;
                    align-items: start;
                    padding: 15px;
                    transition: all 0.3s;
                }

                .notification-item i {
                    margin-right: 10px;
                    font-size: 24px;
                }

                .notification-item div {
                    margin-left: 10px;
                    flex: 1;
                }

                .notification-item h4 {
                    font-size: 16px;
                    font-weight: 600;
                    margin-bottom: 5px;
                }

                .notification-item p {
                    font-size: 13px;
                    margin-bottom: 3px;
                    color: #919191;
                }

                .dropdown-header {
                    background: #f6f9ff;
                    padding: 15px;
                    text-align: center;
                    font-weight: 600;
                }

                .dropdown-footer {
                    text-align: center;
                    padding: 10px;
                    font-size: 15px;
                }

                .dropdown-footer a {
                    color: #444444;
                    text-decoration: none;
                }

                .dropdown-footer a:hover {
                    text-decoration: underline;
                }

                .badge-number {
                    position: absolute;
                    inset: -2px -5px auto auto;
                    font-weight: normal;
                    font-size: 12px;
                    padding: 3px 6px;
                }
            </style>

            <!-- User Nav -->
            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <span class="d-none d-md-block dropdown-toggle ps-2">
                        <?php
                        echo isset($_SESSION['user']['nombre']) ? htmlspecialchars($_SESSION['user']['nombre']) : 'Usuario';
                        ?>
                    </span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>
                            <?php
                            echo isset($_SESSION['user']['nombre']) ? htmlspecialchars($_SESSION['user']['nombre']) : 'Usuario';
                            ?>
                        </h6>
                        <span>
                            <?php
                            echo isset($_SESSION['user']['tipo_usuario']) ? ucfirst(htmlspecialchars($_SESSION['user']['tipo_usuario'])) : 'Rol no definido';
                            ?>
                        </span>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="users-profile.php">
                            <i class="bi bi-person"></i>
                            <span>Mi Perfil</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Cerrar Sesión</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</header>