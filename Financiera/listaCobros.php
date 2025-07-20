<?php
session_start();
require_once 'config.php';
include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Consulta para obtener pagos pendientes
$sql = "SELECT 
    p.id as prestamo_id,
    c.nombre_completo as nombre_cliente,
    p.monto_autorizado as monto,
    p.tasa_interes,
    p.fecha_primer_pago,
    p.fecha_ultimo_pago,
    p.frecuencia_pago,
    p.saldo_restante,
    p.plazo_semanas,
    CASE 
        WHEN p.fecha_primer_pago < CURRENT_DATE THEN 'Vencido'
        WHEN p.fecha_primer_pago = CURRENT_DATE THEN 'Vence Hoy'
        ELSE 'Pendiente'
    END as estado_vencimiento
FROM prestamos p
INNER JOIN clientes c ON p.cliente_id = c.id
WHERE p.estado_solicitud = 'aprobado' 
AND p.saldo_restante > 0
ORDER BY p.fecha_primer_pago ASC  ";

$stmt = $conn->prepare($sql);
$stmt->execute();
$pagos_pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Cobros Pendientes</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Cobros</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Lista de Cobros Pendientes</h5>
                        
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Monto a Pagar</th>
                                    <th>Saldo Restante</th>
                                    <th>Fecha Próximo Pago</th>
                                    <th>Frecuencia</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos_pendientes as $pago): 
                                    $monto_pago = $pago['frecuencia_pago'] == 'quincenal' ? 
                                        $pago['monto'] / (($pago['plazo_semanas'] / 2)) :
                                        $pago['monto'] / $pago['plazo_semanas'];
                                    
                                    $clase_estado = '';
                                    switch ($pago['estado_vencimiento']) {
                                        case 'Vencido':
                                            $clase_estado = 'text-danger fw-bold';
                                            break;
                                        case 'Vence Hoy':
                                            $clase_estado = 'text-warning fw-bold';
                                            break;
                                        default:
                                            $clase_estado = 'text-success';
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pago['nombre_cliente']); ?></td>
                                        <td>$<?php echo number_format($monto_pago, 2); ?></td>
                                        <td>$<?php echo number_format($pago['saldo_restante'], 2); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($pago['fecha_primer_pago'])); ?></td>
                                        <td><?php echo ucfirst($pago['frecuencia_pago']); ?></td>
                                        <td class="<?php echo $clase_estado; ?>">
                                            <?php echo $pago['estado_vencimiento']; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm"
                                                    onclick="mostrarDetallesCobro(<?php echo htmlspecialchars(json_encode($pago)); ?>)">
                                                <i class="bi bi-cash"></i> Registrar Pago
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Modal de Cobro -->
<div class="modal fade" id="modalCobro" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCobro">
                    <input type="hidden" id="prestamo_id" name="prestamo_id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control" id="cliente" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Saldo Restante</label>
                            <input type="text" class="form-control" id="saldo_restante" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Pago</label>
                            <select class="form-select" id="tipo_pago" name="tipo_pago" required>
                                <option value="preferente">Preferente</option>
                                <option value="parcial">Parcial</option>
                                <option value="total">Total</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monto a Pagar</label>
                            <input type="number" class="form-control" id="monto_pago" name="monto_pago" 
                                   step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Pago</label>
                        <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="registrarPago()">Confirmar Pago</button>
            </div>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>

<script>
function mostrarDetallesCobro(pago) {
    console.log('Datos del pago:', pago);
    
    // Llenar los campos del modal
    document.getElementById('prestamo_id').value = pago.prestamo_id;
    document.getElementById('cliente').value = pago.nombre_cliente;
    document.getElementById('saldo_restante').value = formatMoney(pago.saldo_restante);
    
    // Calcular monto del pago según frecuencia
    const montoPago = pago.frecuencia_pago === 'quincenal' ? 
        pago.monto / (pago.plazo_semanas / 2) :
        pago.monto / pago.plazo_semanas;
    
    document.getElementById('monto_pago').value = montoPago.toFixed(2);
    
    // Mostrar el modal
    const modalCobro = new bootstrap.Modal(document.getElementById('modalCobro'));
    modalCobro.show();
}

function registrarPago() {
    // Obtener los datos del formulario
    const formData = {
        prestamo_id: document.getElementById('prestamo_id').value,
        tipo_pago: document.getElementById('tipo_pago').value,
        monto: document.getElementById('monto_pago').value,
        fecha_pago: document.getElementById('fecha_pago').value,
        observaciones: document.getElementById('observaciones').value
    };

    // Validar datos
    if (!formData.monto || parseFloat(formData.monto) <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor ingrese un monto válido'
        });
        return;
    }

    // Mostrar loading
    Swal.fire({
        title: 'Procesando...',
        text: 'Registrando el pago',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Enviar datos al servidor
    fetch('functions/registrar_pago.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Pago registrado correctamente',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Cerrar modal y recargar página
                const modalCobro = bootstrap.Modal.getInstance(document.getElementById('modalCobro'));
                modalCobro.hide();
                location.reload();
            });
        } else {
            throw new Error(data.message || 'Error al registrar el pago');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al procesar la solicitud'
        });
    });
}

// Función auxiliar para formatear montos
function formatMoney(amount) {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Validación en tiempo real del monto
document.getElementById('monto_pago').addEventListener('input', function(e) {
    const monto = parseFloat(e.target.value);
    const saldoRestante = parseFloat(document.getElementById('saldo_restante').value.replace(/[^0-9.-]+/g, ''));
    
    if (isNaN(monto) || monto <= 0 || monto > saldoRestante) {
        e.target.classList.add('is-invalid');
        document.querySelector('.btn-primary').disabled = true;
    } else {
        e.target.classList.remove('is-invalid');
        document.querySelector('.btn-primary').disabled = false;
    }
});
</script>