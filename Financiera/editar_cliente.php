<?php
require_once 'config.php';
require_once 'functions/session_handler.php';
checkSession();

// Verificar si se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: clientes.php');
    exit;
}

$cliente_id = intval($_GET['id']);

try {
    // Obtener datos del cliente
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

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

} catch (PDOException $e) {
    $_SESSION['error'] = "Error al cargar los datos del cliente: " . $e->getMessage();
    header('Location: clientes.php');
    exit;
}

include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Editar Cliente</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="clientes.php">Clientes</a></li>
                <li class="breadcrumb-item active">Editar Cliente</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row mb-3">
            <div class="col-md-12">
                <a href="ver_cliente.php?id=<?php echo $cliente_id; ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <form action="functions/actualizar_cliente.php" method="POST">
            <input type="hidden" name="cliente_id" value="<?php echo $cliente_id; ?>">
            
            <!-- Información Personal -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Información Personal</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" name="nombre_completo" 
                                       value="<?php echo htmlspecialchars($cliente['nombre_completo']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">RFC</label>
                                <input type="text" class="form-control" name="rfc" 
                                       value="<?php echo htmlspecialchars($datos_personales['rfc'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">CURP</label>
                                <input type="text" class="form-control" name="curp" 
                                       value="<?php echo htmlspecialchars($datos_personales['curp'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Estado Civil</label>
                                <select class="form-select" name="estado_civil" required>
                                    <?php
                                    $estados_civiles = ['Soltero', 'Casado', 'Divorciado', 'Viudo', 'Union Libre'];
                                    foreach ($estados_civiles as $estado) {
                                        $selected = ($datos_personales['estado_civil'] ?? '') === $estado ? 'selected' : '';
                                        echo "<option value=\"$estado\" $selected>$estado</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- Más campos de información personal -->
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
                            <div class="mb-3">
                                <label class="form-label">Tipo de Empleo</label>
                                <select class="form-select" name="tipo_empleo" required>
                                    <?php
                                    $tipos_empleo = ['Tiempo completo', 'Medio tiempo', 'Ama de casa', 'Desempleado', 'Negocio propio', 'Retirado', 'Informal'];
                                    foreach ($tipos_empleo as $tipo) {
                                        $selected = ($datos_laborales['tipo_empleo'] ?? '') === $tipo ? 'selected' : '';
                                        echo "<option value=\"$tipo\" $selected>$tipo</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- Continuar con más campos laborales -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos Financieros -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Información Financiera</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Ingresos Mensuales</label>
                                <input type="number" step="0.01" class="form-control" name="ingresos_mensuales" 
                                       value="<?php echo htmlspecialchars($datos_financieros['ingresos_mensuales'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gastos Mensuales</label>
                                <input type="number" step="0.01" class="form-control" name="gastos_mensuales" 
                                       value="<?php echo htmlspecialchars($datos_financieros['gastos_mensuales'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Otros Ingresos</label>
                                <input type="number" step="0.01" class="form-control" name="otros_ingresos" 
                                       value="<?php echo htmlspecialchars($datos_financieros['otros_ingresos'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fuente de Otros Ingresos</label>
                                <input type="text" class="form-control" name="fuente_otros_ingresos" 
                                       value="<?php echo htmlspecialchars($datos_financieros['fuente_otros_ingresos'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Renta Mensual</label>
                                <input type="number" step="0.01" class="form-control" name="renta_mensual" 
                                       value="<?php echo htmlspecialchars($datos_financieros['renta_mensual'] ?? ''); ?>">
                            </div>
                            <!-- Otros gastos -->
                            <div class="mb-3">
                                <label class="form-label">Gastos de Alimentación</label>
                                <input type="number" step="0.01" class="form-control" name="gastos_alimentacion" 
                                       value="<?php echo htmlspecialchars($datos_financieros['gastos_alimentacion'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gastos de Servicios</label>
                                <input type="number" step="0.01" class="form-control" name="gastos_servicios" 
                                       value="<?php echo htmlspecialchars($datos_financieros['gastos_servicios'] ?? ''); ?>">
                            </div>
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
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="servicios[<?php echo $key; ?>]" 
                                               id="<?php echo $key; ?>" 
                                               value="Si" 
                                               <?php echo ($condiciones_vivienda[$key] ?? '') === 'Si' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="<?php echo $key; ?>">
                                            <?php echo $label; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="3"><?php echo htmlspecialchars($condiciones_vivienda['observaciones'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referencias -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Referencias</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tabla-referencias">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Dirección</th>
                                            <th>Teléfono</th>
                                            <th>Parentesco</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($referencias as $index => $referencia): ?>
                                            <tr>
                                                <td>
                                                    <input type="text" class="form-control" 
                                                           name="referencias[<?php echo $index; ?>][nombre]" 
                                                           value="<?php echo htmlspecialchars($referencia['nombre']); ?>" required>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" 
                                                           name="referencias[<?php echo $index; ?>][direccion]" 
                                                           value="<?php echo htmlspecialchars($referencia['direccion']); ?>" required>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" 
                                                           name="referencias[<?php echo $index; ?>][telefono]" 
                                                           value="<?php echo htmlspecialchars($referencia['telefono']); ?>" required>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" 
                                                           name="referencias[<?php echo $index; ?>][parentesco]" 
                                                           value="<?php echo htmlspecialchars($referencia['parentesco']); ?>" required>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm eliminar-referencia">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-success" id="agregar-referencia">
                                <i class="bi bi-plus-circle"></i> Agregar Referencia
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón de guardar -->
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>

        </form>
    </section>
</main>

<script>
// Función para agregar nueva referencia
document.getElementById('agregar-referencia').addEventListener('click', function() {
    const tbody = document.querySelector('#tabla-referencias tbody');
    const index = tbody.children.length;
    const newRow = `
        <tr>
            <td><input type="text" class="form-control" name="referencias[${index}][nombre]" required></td>
            <td><input type="text" class="form-control" name="referencias[${index}][direccion]" required></td>
            <td><input type="text" class="form-control" name="referencias[${index}][telefono]" required></td>
            <td><input type="text" class="form-control" name="referencias[${index}][parentesco]" required></td>
            <td>
                <button type="button" class="btn btn-danger btn-sm eliminar-referencia">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    tbody.insertAdjacentHTML('beforeend', newRow);
});

// Función para eliminar referencia
document.addEventListener('click', function(e) {
    if (e.target.closest('.eliminar-referencia')) {
        if (confirm('¿Está seguro de eliminar esta referencia?')) {
            e.target.closest('tr').remove();
        }
    }
});
</script>

<style>
    .main {
        padding: 20px;
        margin-top: 60px;
        min-height: calc(100vh - 60px);
    }
    
    .card {
        margin-bottom: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    
    .form-label {
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .main {
            padding: 15px;
        }
    }
</style>

<?php include 'views/footer.php'; ?> 