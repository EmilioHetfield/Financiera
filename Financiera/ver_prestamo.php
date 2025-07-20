<?php
session_start();
include 'config.php';
include 'functions/dashboard_queries.php';
require_once 'functions/auth.php';
verificarAcceso(['master', 'vendedor']);

// Verificar que se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: ver_prestamos.php');
    exit;
}

$prestamo_id = (int)$_GET['id'];

// Obtener detalles del préstamo
require_once 'functions/prestamo_functions.php';
$prestamoManager = new PrestamoManager($conn);
$prestamo = $prestamoManager->obtenerPrestamo($prestamo_id);

if (!$prestamo) {
    $_SESSION['error'] = "Préstamo no encontrado";
    header('Location: ver_prestamos.php');
    exit;
}

include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Detalles del Préstamo</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="ver_prestamos.php">Préstamos</a></li>
                <li class="breadcrumb-item active">Detalles del Préstamo #<?php echo $prestamo['id']; ?></li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Información del Préstamo</h5>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Información General</h6>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">ID Préstamo:</th>
                                        <td><?php echo $prestamo['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Estado:</th>
                                        <td>
                                            <span class="badge <?php echo $prestamo['estado'] === 'Completado' ? 'bg-success' : 'bg-warning'; ?>">
                                                <?php echo $prestamo['estado']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Estado Solicitud:</th>
                                        <td>
                                            <span class="badge <?php 
                                                echo match($prestamo['estado_solicitud']) {
                                                    'aprobado' => 'bg-success',
                                                    'rechazado' => 'bg-danger',
                                                    default => 'bg-warning'
                                                };
                                            ?>">
                                                <?php echo ucfirst($prestamo['estado_solicitud']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Fecha Solicitud:</th>
                                        <td><?php echo date('d/m/Y H:i', strtotime($prestamo['fecha_solicitud'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Detalles Financieros</h6>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Monto:</th>
                                        <td>$<?php echo number_format($prestamo['monto'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Plazo:</th>
                                        <td><?php echo $prestamo['plazo']; ?> meses</td>
                                    </tr>
                                    <tr>
                                        <th>Tasa de Interés:</th>
                                        <td><?php echo $prestamo['tasa_interes']; ?>%</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Información del Cliente</h6>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Nombre:</th>
                                        <td><?php echo $prestamo['nombre_cliente']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Teléfono:</th>
                                        <td><?php echo $prestamo['telefono_cliente']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Firma del Préstamo</h6>
                                <div class="text-center">
                                    <?php if (!empty($prestamo['ruta_firma_prestamo'])): ?>
                                        <?php
                                        // Debug de la ruta
                                        error_log("Ruta completa de la firma: " . $prestamo['ruta_firma_prestamo']);
                                        
                                        // Obtener el nombre del archivo de la ruta completa
                                        $nombreArchivo = basename($prestamo['ruta_firma_prestamo']);
                                        error_log("Nombre del archivo: " . $nombreArchivo);
                                        
                                        // Verificar si el archivo existe
                                        $rutaCompleta = "uploads/firmas/" . $nombreArchivo;
                                        if (file_exists($rutaCompleta)) {
                                            error_log("El archivo existe en: " . $rutaCompleta);
                                        } else {
                                            error_log("El archivo NO existe en: " . $rutaCompleta);
                                        }
                                        ?>
                                        <img src="<?php echo $rutaCompleta; ?>" 
                                             alt="Firma del préstamo" 
                                             class="img-fluid border rounded"
                                             style="max-height: 200px;"
                                             onerror="this.onerror=null; this.src='assets/images/no-signature.png'; this.alt='Firma no disponible';">
                                    <?php else: ?>
                                        <img src="assets/images/no-signature.png" 
                                             alt="Sin firma" 
                                             class="img-fluid border rounded"
                                             style="max-height: 200px;">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="ver_prestamos.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <?php if ($prestamo['estado_solicitud'] === 'aprobado' && $prestamo['estado'] === 'Pendiente'): ?>
                                <a href="pagareA.php?prestamo_id=<?php echo $prestamo['id']; ?>" class="btn btn-success">
                                    <i class="bi bi-file-earmark-text"></i> Generar Pagaré
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'views/footer.php'; ?> 