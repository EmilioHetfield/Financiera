<?php
ob_start();
require_once 'functions/session_handler.php';
checkSession();

include 'config.php';
include 'functions/dashboard_queries.php';

if (!isset($_SESSION['user'])) {
    header("Location: pages-login.html");
    exit();
}

$user_info = $_SESSION['user'];
$dashboard_data = getDashboardData($conn);

// Asignar valores con verificación
$totals = $dashboard_data['totals'] ?? [];
$notifications = $dashboard_data['notifications'] ?? [];
$worker_loans = $dashboard_data['worker_loans'] ?? [];
$client_loans = $dashboard_data['client_loans'] ?? [];

// Cuando uses los datos en el HTML
if (!empty($totals)) {
    $total_clientes = $totals['total_clientes'] ?? 0;
    $total_prestamos = $totals['total_prestamos'] ?? 0;
    $monto_total = $totals['monto_total'] ?? 0;
} else {
    $total_clientes = $total_prestamos = $monto_total = 0;
}

// Para los bucles foreach
if (!empty($notifications)):
    foreach ($notifications as $notification):
    // tu código
    endforeach;
endif;

if (!empty($client_loans)):
    foreach ($client_loans as $loan):
    // tu código
    endforeach;
endif;

?>

<?php include '../Financiera/views/head.php'; ?>

<?php include '../Financiera/views/header.php'; ?>

<?php include '../Financiera/views/nav_menu.php'; ?>
<!-- ======= Main ======= -->

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <?php if (in_array($user_info['tipo_usuario'], ['master'])): ?>
                        <!-- Sección de estadísticas -->
                        <div class="row">
                            <!-- Recuadro para Total de Clientes -->
                            <div class="col-xxl-4 col-md-4">
                                <div class="card info-card customers-card">
                                    <div class="filter"></div>
                                    <div class="card-body">
                                        <h5 class="card-title">Clientes <span>| Total</span></h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-people"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6 id="total-clients"><?php echo htmlspecialchars($totals['total_clients']); ?></h6>
                                                <span class="text-success small pt-1 fw-bold">Clientes</span> <span class="text-muted small pt-2 ps-1">Registrados</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recuadro para Total de Vendedores -->
                            <div class="col-xxl-4 col-md-4">
                                <div class="card info-card sellers-card">
                                    <div class="filter"></div>
                                    <div class="card-body">
                                        <h5 class="card-title">Vendedores <span>| Total</span></h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-people"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6 id="total-sellers"><?php echo htmlspecialchars($totals['total_sellers']); ?></h6>
                                                <span class="text-success small pt-1 fw-bold">Vendedores</span> <span class="text-muted small pt-2 ps-1">Registrados</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recuadro para Total de Cobradores -->
                            <div class="col-xxl-4 col-md-4">
                                <div class="card info-card collectors-card">
                                    <div class="filter"></div>
                                    <div class="card-body">
                                        <h5 class="card-title">Cobradores <span>| Total</span></h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-people"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6 id="total-collectors"><?php echo htmlspecialchars($totals['total_collectors']); ?></h6>
                                                <span class="text-success small pt-1 fw-bold">Cobradores</span> <span class="text-muted small pt-2 ps-1">Registrados</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Trabajadores <span>| Todos</span></h5>

                                    <div class="table-responsive">
                                        <table class="table table-borderless table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Nombre</th>
                                                    <th scope="col">Teléfono</th>
                                                    <th scope="col">Tipo</th>
                                                    <th scope="col">Usuario</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($worker_loans)): ?>
                                                    <?php foreach ($worker_loans as $worker): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($worker['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($worker['full_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($worker['mobile_number']); ?></td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $worker['badge_color']; ?>">
                                                                    <?php echo ucfirst(htmlspecialchars($worker['id_type'])); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($worker['nickname']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No hay datos disponibles</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Tabla de Trabajadores -->


                    <!-- Tabla de Préstamos de Clientes -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    Préstamos
                                    <span>|
                                        <?php
                                        if ($user_info['tipo_usuario'] === 'vendedor') {
                                            echo 'Mis Clientes';
                                        } else {
                                            echo 'Todos los Clientes';
                                        }
                                        ?>
                                    </span>
                                </h5>

                                <div class="table-responsive">
                                    <table class="table table-borderless table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Nombre</th>
                                                <th scope="col">ID Cliente</th>
                                                <th scope="col">Monto Solicitado</th>
                                                <th scope="col">Monto Autorizado</th>
                                                <th scope="col">Estado</th>
                                                <th scope="col">Fecha</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($client_loans)): ?>
                                                <?php foreach ($client_loans as $loan): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($loan['id']); ?></td>
                                                        <td><?php echo htmlspecialchars($loan['first_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($loan['client_number']); ?></td>
                                                        <td>
                                                            <span class="text-primary">
                                                                $<?php echo number_format($loan['requested_amount'], 2); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success">
                                                                $<?php echo number_format($loan['authorized_amount'] ?? 0, 2); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php
                                                                                    echo match ($loan['estado_solicitud']) {
                                                                                        'pendiente' => 'warning',
                                                                                        'aprobado' => 'success',
                                                                                        'rechazado' => 'danger',
                                                                                        default => 'secondary'
                                                                                    };
                                                                                    ?>">
                                                                <?php echo ucfirst(htmlspecialchars($loan['estado_solicitud'])); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('d/m/Y', strtotime($loan['fecha_solicitud'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No hay préstamos disponibles</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
</body>
<?php include '../Financiera/views/footer.php'; ?>

</html>