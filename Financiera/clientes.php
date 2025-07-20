<?php
require_once 'config.php';
require_once 'functions/session_handler.php';
checkSession();

// Obtener el tipo de usuario y su ID
$es_admin = in_array($_SESSION['user']['tipo_usuario'], ['master', 'autorizador']);
$usuario_id = $_SESSION['user']['id'];

try {
    // Debug de información de usuario
    error_log("Tipo de usuario: " . $_SESSION['user']['tipo_usuario']);
    error_log("ID de usuario: " . $usuario_id);

    // Consulta SQL base
    $sql = "SELECT 
        c.*,
        COALESCE(p.total_prestamos, 0) as total_prestamos,
        u.nombre as nombre_vendedor
    FROM clientes c
    LEFT JOIN usuarios u ON c.id_vendedor = u.id
    LEFT JOIN (
        SELECT cliente_id, COUNT(*) as total_prestamos 
        FROM prestamos 
        GROUP BY cliente_id
    ) p ON c.id = p.cliente_id";

    // Si no es master ni autorizador, filtrar por vendedor
    if (!$es_admin) {
        $sql .= " WHERE c.id_vendedor = :usuario_id";
    }

    $sql .= " ORDER BY c.id DESC";

    // Debug de la consulta
    error_log("SQL Query: " . $sql);

    $stmt = $conn->prepare($sql);

    if (!$es_admin) {
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    }

    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug del resultado
    error_log("Número de clientes encontrados: " . count($clientes));
} catch (PDOException $e) {
    error_log("Error en consulta de clientes: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    $_SESSION['error'] = "Error al cargar los clientes: " . $e->getMessage();
    $clientes = [];
}

// Verifica el tipo de usuario en la sesión
error_log("Tipo de usuario: " . $_SESSION['user']['tipo_usuario']);

include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Gestión de Clientes</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Clientes</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            Lista de Clientes
                            <a href="registrocliente.php" class="btn btn-primary float-end">
                                <i class="bi bi-person-plus"></i> Nuevo Cliente
                            </a>
                        </h5>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Teléfono</th>
                                        <?php if ($es_admin): ?>
                                            <th>Vendedor</th>
                                        <?php endif; ?>
                                        <th>Préstamos</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <tr>
                                            <td><?php echo $cliente['id']; ?></td>
                                            <td><?php echo htmlspecialchars($cliente['nombre_completo']); ?></td>
                                            <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                            <?php if ($es_admin): ?>
                                                <td><?php echo htmlspecialchars($cliente['nombre_vendedor']); ?></td>
                                            <?php endif; ?>
                                            <td><?php echo $cliente['total_prestamos']; ?></td>
                                            <td>
                                                <?php if ($es_admin): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm <?php echo $cliente['estado'] === 'Activo' ? 'btn-success' : 'btn-danger'; ?>" 
                                                            onclick="cambiarEstadoCliente(<?php echo $cliente['id']; ?>, '<?php echo $cliente['estado'] === 'Activo' ? 'Inactivo' : 'Activo'; ?>')"
                                                            title="Cambiar estado">
                                                        <i class="bi <?php echo $cliente['estado'] === 'Activo' ? 'bi-check-circle' : 'bi-x-circle'; ?>"></i>
                                                        <?php echo $cliente['estado']; ?>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="badge bg-<?php echo $cliente['estado'] === 'Activo' ? 'success' : 'danger'; ?>">
                                                        <?php echo $cliente['estado']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="ver_cliente.php?id=<?php echo $cliente['id']; ?>" 
                                                       class="btn btn-info btn-sm" title="Ver detalles">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <?php if ($es_admin): ?>
                                                        <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>" 
                                                           class="btn btn-warning btn-sm" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="documentos_cliente.php?id=<?php echo $cliente['id']; ?>" 
                                                       class="btn btn-secondary btn-sm" title="Documentos">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-primary btn-sm" 
                                                            title="Historial de Préstamos"
                                                            onclick="verHistorialPrestamos(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nombre_completo']); ?>')">
                                                        <i class="bi bi-clock-history"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Modal de Geolocalización -->
<!--
<div class="modal fade" id="modalGeolocalizacion" tabindex="-1" aria-labelledby="modalGeolocalizacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGeolocalizacionLabel">Ubicación del Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="map" style="height: 400px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
-->

<!-- <script src="https://maps.googleapis.com/maps/api/js?key=TU_API_KEY"></script> -->
<script>
    function confirmarEliminacion(clienteId) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `functions/eliminar_cliente.php?id=${clienteId}`;
            }
        });
    }

    function cambiarEstadoCliente(clienteId, nuevoEstado) {
        const estadoOpuesto = nuevoEstado === 'Activo' ? 'Inactivo' : 'Activo';
        const mensaje = `¿Está seguro que desea cambiar el estado del cliente a ${nuevoEstado}?`;
        
        Swal.fire({
            title: 'Cambiar Estado',
            text: mensaje,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: nuevoEstado === 'Activo' ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Sí, cambiar a ${nuevoEstado}`,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', clienteId);
                formData.append('estado', nuevoEstado);

                fetch('functions/cambiar_estado_cliente.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Estado Actualizado',
                            text: `El cliente ha sido ${nuevoEstado === 'Activo' ? 'activado' : 'desactivado'} correctamente`,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Error al cambiar el estado');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Error al cambiar el estado del cliente'
                    });
                });
            }
        });
    }

    // Inicializar DataTable
    $(document).ready(function() {
        $('.datatable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [
                [0, "desc"]
            ]
        });
    });

    function verHistorialPrestamos(clienteId, nombreCliente) {
        document.getElementById('clienteNombre').textContent = nombreCliente;
        
        const modal = new bootstrap.Modal(document.getElementById('modalHistorialPrestamos'));
        modal.show();
        
        fetch(`functions/obtener_historial_prestamos.php?cliente_id=${clienteId}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('historialPrestamosBody');
                tbody.innerHTML = '';
                
                data.forEach(prestamo => {
                    const row = `
                        <tr>
                            <td>${prestamo.id}</td>
                            <td>$${parseFloat(prestamo.monto).toFixed(2)}</td>
                            <td>${new Date(prestamo.fecha_solicitud).toLocaleDateString()}</td>
                            <td>
                                <span class="badge bg-${getEstadoColor(prestamo.estado_solicitud)}">
                                    ${prestamo.estado_solicitud}
                                </span>
                            </td>
                            <td>$${parseFloat(prestamo.monto_autorizado || 0).toFixed(2)}</td>
                            <td>${prestamo.plazo_semanas || '-'} semanas</td>
                            <td>$${parseFloat(prestamo.saldo_restante || 0).toFixed(2)}</td>
                            <td>
                                ${prestamo.puede_renovar ? `
                                    <button type="button" 
                                            class="btn btn-success btn-sm"
                                            onclick="iniciarRenovacion(${clienteId}, ${prestamo.id}, '${nombreCliente}')">
                                        <i class="bi bi-arrow-repeat"></i> Renovar
                                    </button>
                                ` : ''}
                            </td>
                            <td>
                                <a href="./functions/generar_pdf.php?id_prestamo=${prestamo.id}" 
                                    class="btn btn-info btn-sm" target="_blank" title="Generar Contrato">
                                <i class="bi bi-file-earmark-pdf"></i> Contrato
                                </a>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar el historial de préstamos');
            });
    }

    function iniciarRenovacion(clienteId, prestamoId, nombreCliente) {
        Swal.fire({
            title: 'Renovar Préstamo',
            html: `¿Desea iniciar una renovación de préstamo para ${nombreCliente}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, renovar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `solicitar_prestamo.php?cliente_id=${clienteId}&renovacion=${prestamoId}`;
            }
        });
    }

    function getEstadoColor(estado) {
        switch(estado.toLowerCase()) {
            case 'aprobado':
                return 'success';
            case 'pendiente':
                return 'warning';
            case 'rechazado':
                return 'danger';
            default:
                return 'secondary';
        }
    }
</script>

<!-- Actualizar el modal para incluir la columna de acciones -->
<div class="modal fade" id="modalHistorialPrestamos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Historial de Préstamos - <span id="clienteNombre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Monto</th>
                                <th>Fecha Solicitud</th>
                                <th>Estado</th>
                                <th>Monto Autorizado</th>
                                <th>Plazo</th>
                                <th>Saldo Restante</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="historialPrestamosBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>