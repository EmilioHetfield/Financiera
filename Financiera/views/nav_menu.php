<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <!-- Dashboard siempre visible para todos -->
        <li class="nav-item">
            <a class="nav-link " href="index.php">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="users-profile.php">
                <i class="bi bi-person"></i>
                <span>Profile</span>
            </a>
        </li>

        <li class="nav-heading">Pages</li>

        <?php if ($_SESSION['user']['tipo_usuario'] === 'master'): ?>
            <!-- Opciones solo para master -->

            <li class="nav-item">
                <a class="nav-link collapsed" href="registrousuario.php">
                    <i class="bi bi-card-list"></i>
                    <span>Registrar Usuario</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="registrocliente.php">
                    <i class="bi bi-card-list"></i>
                    <span>Registrar Cliente</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="pagareA.php">
                    <i class="bi bi-card-list"></i>
                    <span>Pagare</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="listaCobros.php">
                    <i class="bi bi-card-list"></i>
                    <span>Lista Cobros</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="ver_prestamos.php">
                    <i class="bi bi-card-list"></i>
                    <span>Lista Prestamos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="autorizar_prestamos.php">
                    <i class="bi bi-card-list"></i>
                    <span>Autorizar Prestamos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="cambiar_passwords.php">
                    <i class="bi bi-card-list"></i>
                    <span>Cambiar contraseña</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="clientes.php">
                    <i class="bi bi-card-list"></i>
                    <span>Clientes</span>
                </a>
            </li>

        <?php elseif ($_SESSION['user']['tipo_usuario'] === 'vendedor'): ?>
            <!-- Opciones para vendedor/cobrador -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="registrocliente.php">
                    <i class="bi bi-card-list"></i>
                    <span>Registrar Cliente</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="pagareA.php">
                    <i class="bi bi-card-list"></i>
                    <span>Pagare</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="listaCobros.php">
                    <i class="bi bi-card-list"></i>
                    <span>Lista Cobros</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="clientes.php">
                    <i class="bi bi-card-list"></i>
                    <span>Clientes</span>
                </a>
            </li>

        <?php elseif ($_SESSION['user']['tipo_usuario'] === 'autorizador'): ?>
            <!-- Opciones solo para autorizador -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="autorizar_prestamos.php">
                    <i class="bi bi-card-list"></i>
                    <span>Lista Prestamos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="clientes.php">
                    <i class="bi bi-card-list"></i>
                    <span>Clientes</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</aside>