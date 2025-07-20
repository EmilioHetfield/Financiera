<?php
require_once 'config.php';
require_once 'functions/session_handler.php';
checkSession();
include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';

// Verificar si se proporcionó un ID de cliente
if (!isset($_GET['id'])) {
    header('Location: clientes.php');
    exit;
}

$cliente_id = intval($_GET['id']);

// Obtener información del cliente
$stmt = $conn->prepare("
    SELECT 
        c.*,
        u.nombre as nombre_vendedor,
        COALESCE(p.total_prestamos, 0) as total_prestamos
    FROM clientes c
    LEFT JOIN usuarios u ON c.id_vendedor = u.id
    LEFT JOIN (
        SELECT cliente_id, COUNT(*) as total_prestamos 
        FROM prestamos 
        GROUP BY cliente_id
    ) p ON c.id = p.cliente_id
    WHERE c.id = ?
");

$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    header('Location: clientes.php');
    exit;
}

// Obtener documentos del cliente
$stmt = $conn->prepare("
    SELECT * FROM documentos 
    WHERE cliente_id = ? 
    ORDER BY fecha_subida DESC
");
$stmt->execute([$cliente_id]);
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si el usuario es administrador
$es_admin = $_SESSION['user']['tipo_usuario'] === 'master';

// Obtener datos personales
$stmt = $conn->prepare("SELECT * FROM datos_personales WHERE id_cliente = ?");
$stmt->execute([$cliente_id]);
$datos_personales = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener datos laborales
$stmt = $conn->prepare("SELECT * FROM datos_laborales WHERE id_cliente = ?");
$stmt->execute([$cliente_id]);
$datos_laborales = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener datos financieros
$stmt = $conn->prepare("SELECT * FROM datos_financieros WHERE id_cliente = ?");
$stmt->execute([$cliente_id]);
$datos_financieros = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener condiciones de vivienda
$stmt = $conn->prepare("SELECT * FROM condiciones_vivienda WHERE id_cliente = ?");
$stmt->execute([$cliente_id]);
$condiciones_vivienda = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener dirección
$stmt = $conn->prepare("SELECT * FROM direcciones WHERE cliente_id = ?");
$stmt->execute([$cliente_id]);
$direccion = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener referencias
$stmt = $conn->prepare("SELECT * FROM referencias WHERE id_datos_personales = ?");
$stmt->execute([$datos_personales['id'] ?? 0]);
$referencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener dependientes
$stmt = $conn->prepare("SELECT * FROM dependientes WHERE id_datos_personales = ?");
$stmt->execute([$datos_personales['id'] ?? 0]);
$dependientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Detalles del Cliente</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="clientes.php">Clientes</a></li>
                <li class="breadcrumb-item active">Detalles</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <!-- Información Personal -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Información Personal</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 40%">Nombre Completo:</th>
                                    <td><?php echo htmlspecialchars($cliente['nombre_completo']); ?></td>
                                </tr>
                                <tr>
                                    <th>Fecha de Nacimiento:</th>
                                    <td><?php echo date('d/m/Y', strtotime($cliente['fecha_nacimiento'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                </tr>
                                <tr>
                                    <th>Teléfono:</th>
                                    <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                </tr>
                                <tr>
                                    <th>Género:</th>
                                    <td><?php echo $cliente['genero'] === 'M' ? 'Masculino' : 'Femenino'; ?></td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        <span class="badge bg-<?php echo $cliente['estado'] === 'Activo' ? 'success' : 'danger'; ?>">
                                            <?php echo $cliente['estado']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Vendedor Asignado:</th>
                                    <td><?php echo htmlspecialchars($cliente['nombre_vendedor'] ?? 'No asignado'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentos -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Documentos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($documentos)): ?>
                            <p class="text-muted">No hay documentos disponibles</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Nombre</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documentos as $documento): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($documento['tipo_documento']); ?></td>
                                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <?php echo htmlspecialchars($documento['nombre_archivo']); ?>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($documento['fecha_subida'])); ?></td>
                                                <td>
                                                    <a href="<?php echo htmlspecialchars($documento['ruta']); ?>" 
                                                       class="btn btn-info btn-sm" 
                                                       target="_blank"
                                                       title="Ver documento">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="row">
            <!-- Datos Personales -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Datos Personales</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($datos_personales): ?>
                            <table class="table table-borderless">
                                <tr><th>RFC:</th><td><?php echo htmlspecialchars($datos_personales['rfc']); ?></td></tr>
                                <tr><th>CURP:</th><td><?php echo htmlspecialchars($datos_personales['curp']); ?></td></tr>
                                <tr><th>Estado Civil:</th><td><?php echo htmlspecialchars($datos_personales['estado_civil']); ?></td></tr>
                                <tr><th>Identificación:</th><td><?php echo htmlspecialchars($datos_personales['tipo_identificacion']); ?></td></tr>
                                <!-- Más campos... -->
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No hay datos personales registrados</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Datos Laborales -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Información Laboral</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($datos_laborales): ?>
                            <table class="table table-borderless">
                                <tr><th>Tipo de Empleo:</th><td><?php echo htmlspecialchars($datos_laborales['tipo_empleo']); ?></td></tr>
                                <tr><th>Ocupación:</th><td><?php echo htmlspecialchars($datos_laborales['ocupacion']); ?></td></tr>
                                <tr><th>Empresa:</th><td><?php echo htmlspecialchars($datos_laborales['nombre_empresa']); ?></td></tr>
                                <!-- Más campos... -->
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No hay datos laborales registrados</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Datos Financieros -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Información Financiera</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($datos_financieros): ?>
                            <table class="table table-borderless">
                                <tr><th>Ingresos Mensuales:</th><td>$<?php echo number_format($datos_financieros['ingresos_mensuales'], 2); ?></td></tr>
                                <tr><th>Gastos Mensuales:</th><td>$<?php echo number_format($datos_financieros['gastos_mensuales'], 2); ?></td></tr>
                                <!-- Más campos... -->
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No hay datos financieros registrados</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Condiciones de Vivienda -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Condiciones de Vivienda</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($condiciones_vivienda): ?>
                            <div class="row">
                                <?php
                                $servicios = [
                                    'internet' => 'Internet',
                                    'telefono_fijo' => 'Teléfono Fijo',
                                    'telefono_movil' => 'Teléfono Móvil',
                                    'refrigerador' => 'Refrigerador',
                                    'luz_electrica' => 'Luz Eléctrica',
                                    'agua_potable' => 'Agua Potable',
                                    'auto_propio' => 'Auto Propio',
                                    'tv_cable' => 'TV Cable',
                                    'alumbrado_publico' => 'Alumbrado Público',
                                    'estufa' => 'Estufa',
                                    'gas' => 'Gas'
                                ];
                                foreach ($servicios as $key => $label): ?>
                                    <div class="col-md-6 mb-2">
                                        <i class="bi <?php echo $condiciones_vivienda[$key] === 'Si' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger'; ?>"></i>
                                        <?php echo $label; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No hay datos de vivienda registrados</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Referencias y Dependientes -->
    <section class="section">
        <div class="row">
            <!-- Referencias -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Referencias</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($referencias)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Parentesco</th>
                                            <th>Teléfono</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($referencias as $referencia): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($referencia['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($referencia['parentesco']); ?></td>
                                                <td><?php echo htmlspecialchars($referencia['telefono']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No hay referencias registradas</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Dependientes -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Dependientes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dependientes)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Parentesco</th>
                                            <th>Ocupación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dependientes as $dependiente): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($dependiente['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($dependiente['parentesco']); ?></td>
                                                <td><?php echo htmlspecialchars($dependiente['ocupacion'] ?? 'No especificada'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No hay dependientes registrados</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Dirección -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Dirección</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($direccion): ?>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Dirección:</th>
                                    <td><?php echo htmlspecialchars($direccion['direccion']); ?></td>
                                </tr>
                                <tr>
                                    <th>Ciudad:</th>
                                    <td><?php echo htmlspecialchars($direccion['ciudad']); ?></td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td><?php echo htmlspecialchars($direccion['estado']); ?></td>
                                </tr>
                                <tr>
                                    <th>Código Postal:</th>
                                    <td><?php echo htmlspecialchars($direccion['codigo_postal']); ?></td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No hay dirección registrada</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Información del Cónyuge -->
            <?php if ($datos_personales && !empty($datos_personales['nombre_conyuge'])): ?>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Información del Cónyuge</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th>Nombre:</th>
                                <td><?php echo htmlspecialchars($datos_personales['nombre_conyuge']); ?></td>
                            </tr>
                            <tr>
                                <th>Fecha de Nacimiento:</th>
                                <td><?php echo date('d/m/Y', strtotime($datos_personales['fecha_nac_conyuge'])); ?></td>
                            </tr>
                            <tr>
                                <th>Teléfono:</th>
                                <td><?php echo htmlspecialchars($datos_personales['telefono_conyuge']); ?></td>
                            </tr>
                            <tr>
                                <th>Ocupación:</th>
                                <td><?php echo htmlspecialchars($datos_personales['ocupacion_conyuge']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<style>
    .main {
        padding: 20px;
        margin-top: 60px;
        min-height: calc(100vh - 60px);
    }
    
    .card {
        margin-bottom: 1rem;
        height: 100%;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    @media (max-width: 768px) {
        .main {
            padding: 15px;
        }
        
        .card {
            margin-bottom: 15px;
        }
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1rem;
    }
    
    .table th {
        width: 40%;
        font-weight: 600;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .bi {
        margin-right: 8px;
    }
    
    @media (max-width: 768px) {
        .table th {
            width: 50%;
        }
    }
</style>

<?php include 'views/footer.php'; ?>