<?php
require_once 'config.php';
require_once 'functions/session_handler.php';
checkSession();

// Verificar si es una renovación
$es_renovacion = isset($_GET['renovacion']) && isset($_GET['cliente_id']);
$cliente_id = $es_renovacion ? intval($_GET['cliente_id']) : null;
$prestamo_anterior_id = $es_renovacion ? intval($_GET['renovacion']) : null;

if ($es_renovacion) {
    // Obtener datos del cliente y del préstamo anterior
    try {
        // Datos del cliente
        $stmt = $conn->prepare("
            SELECT c.*, d.direccion, d.ciudad, d.estado, d.codigo_postal
            FROM clientes c
            LEFT JOIN direcciones d ON c.id = d.cliente_id
            WHERE c.id = ?
        ");
        $stmt->execute([$cliente_id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        // Datos del préstamo anterior
        $stmt = $conn->prepare("
            SELECT monto_autorizado, plazo_semanas
            FROM prestamos
            WHERE id = ? AND cliente_id = ?
        ");
        $stmt->execute([$prestamo_anterior_id, $cliente_id]);
        $prestamo_anterior = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al cargar los datos: " . $e->getMessage();
        header('Location: clientes.php');
        exit;
    }
}

include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1><?php echo $es_renovacion ? 'Renovar Préstamo' : 'Solicitar Préstamo'; ?></h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="clientes.php">Clientes</a></li>
                <li class="breadcrumb-item active">Renovar Préstamo</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <?php if ($es_renovacion && $cliente): ?>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Información del Cliente</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Nombre:</th>
                                    <td><?php echo htmlspecialchars($cliente['nombre_completo']); ?></td>
                                </tr>
                                <tr>
                                    <th>Teléfono:</th>
                                    <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                </tr>
                                <tr>
                                    <th>Dirección:</th>
                                    <td>
                                        <?php 
                                        echo htmlspecialchars($cliente['direccion'] ?? '');
                                        if ($cliente['ciudad'] ?? false) {
                                            echo ", " . htmlspecialchars($cliente['ciudad']);
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Detalles de la Renovación</h5>
                        </div>
                        <div class="card-body">
                            <form action="functions/procesar_prestamo.php" method="POST">
                                <input type="hidden" name="cliente_id" value="<?php echo $cliente_id; ?>">
                                <input type="hidden" name="es_renovacion" value="1">
                                <input type="hidden" name="prestamo_anterior_id" value="<?php echo $prestamo_anterior_id; ?>">

                                <div class="mb-3">
                                    <label class="form-label">Tipo de Préstamo</label>
                                    <select class="form-select" name="tipo_prestamo" required>
                                        <option value="normal">Normal</option>
                                        <option value="aval">Con Aval</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Monto Solicitado</label>
                                    <input type="number" 
                                           class="form-control" 
                                           name="monto" 
                                           value="<?php echo $prestamo_anterior['monto_autorizado'] ?? ''; ?>"
                                           required>
                                    <small class="text-muted">Monto anterior autorizado: $<?php echo number_format($prestamo_anterior['monto_autorizado'], 2); ?></small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Plazo en Semanas</label>
                                    <select class="form-select" name="plazo_semanas" required>
                                        <?php
                                        $plazos = [12, 16, 20, 24];
                                        foreach ($plazos as $plazo) {
                                            $selected = ($prestamo_anterior['plazo_semanas'] ?? 0) == $plazo ? 'selected' : '';
                                            echo "<option value=\"$plazo\" $selected>$plazo semanas</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div id="seccionAval" style="display: none;">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Información del Aval</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nombre del Aval *</label>
                                                        <input type="text" class="form-control" id="nombre_aval" name="nombre_aval">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Teléfono del Aval *</label>
                                                        <input type="tel" class="form-control" id="telefono_aval" name="telefono_aval">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Dirección del Aval *</label>
                                                <input type="text" class="form-control" id="direccion_aval" name="direccion_aval">
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label">Firma del Aval *</label>
                                                <div class="signature-container">
                                                    <canvas id="signatureCanvasAval"></canvas>
                                                </div>
                                                <div class="firma-buttons mt-2">
                                                    <button type="button" class="btn btn-secondary btn-sm" onclick="limpiarFirma('signatureCanvasAval')">
                                                        <i class="bi bi-eraser"></i> Limpiar Firma del Aval
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Firma del Cliente *</label>
                                    <div class="signature-container">
                                        <canvas id="signatureCanvasCliente"></canvas>
                                    </div>
                                    <div class="firma-buttons mt-2">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="limpiarFirma('signatureCanvasCliente')">
                                            <i class="bi bi-eraser"></i> Limpiar Firma
                                        </button>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Solicitar Renovación
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                No se encontraron los datos necesarios para la renovación.
            </div>
        <?php endif; ?>
    </section>
</main>

<style>
    .card {
        margin-bottom: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .table th {
        font-weight: 600;
        color: #495057;
    }
    
    .form-label {
        font-weight: 500;
    }

    .signature-container {
        position: relative;
        width: 100%;
        height: 200px;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        background-color: #fff;
        overflow: hidden;
    }

    #signatureCanvasCliente {
        width: 100%;
        height: 100%;
        touch-action: none;
        cursor: crosshair;
    }

    @media (max-width: 768px) {
        .signature-container {
            height: 150px;
        }
    }
</style>

<script src="assets/js/signature_pad.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let signaturePads = {};

    function setupCanvas(canvasId) {
        const canvas = document.getElementById(canvasId);
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        function resizeCanvas() {
            const container = canvas.parentElement;
            const rect = container.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000000';
        }

        function startDrawing(e) {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            [lastX, lastY] = getCoordinates(e, rect);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();

            const rect = canvas.getBoundingClientRect();
            const [currentX, currentY] = getCoordinates(e, rect);

            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(currentX, currentY);
            ctx.stroke();

            [lastX, lastY] = [currentX, currentY];
        }

        function stopDrawing() {
            isDrawing = false;
        }

        function getCoordinates(e, rect) {
            let x, y;
            if (e.type.includes('touch')) {
                x = e.touches[0].clientX - rect.left;
                y = e.touches[0].clientY - rect.top;
            } else {
                x = e.clientX - rect.left;
                y = e.clientY - rect.top;
            }
            return [x, y];
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            startDrawing(e);
        }, { passive: false });
        
        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            draw(e);
        }, { passive: false });
        
        canvas.addEventListener('touchend', stopDrawing);

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        signaturePads[canvasId] = {
            canvas: canvas,
            ctx: ctx,
            clear: function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            },
            isEmpty: function() {
                const pixels = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
                return !pixels.some(pixel => pixel !== 0);
            },
            toDataURL: function() {
                return canvas.toDataURL('image/png');
            }
        };
    }

    window.limpiarFirma = function(canvasId) {
        if (signaturePads[canvasId]) {
            signaturePads[canvasId].clear();
        }
    };

    setupCanvas('signatureCanvasCliente');

    // Mostrar/ocultar sección del aval según el tipo de préstamo
    document.querySelector('select[name="tipo_prestamo"]').addEventListener('change', function() {
        const seccionAval = document.getElementById('seccionAval');
        const camposAval = ['nombre_aval', 'telefono_aval', 'direccion_aval'];
        
        if (this.value === 'aval') {
            seccionAval.style.display = 'block';
            // Hacer campos obligatorios
            camposAval.forEach(campo => {
                document.getElementById(campo).required = true;
            });
            // Inicializar canvas del aval
            setupCanvas('signatureCanvasAval');
        } else {
            seccionAval.style.display = 'none';
            // Quitar obligatoriedad y limpiar campos
            camposAval.forEach(campo => {
                const elemento = document.getElementById(campo);
                elemento.required = false;
                elemento.value = '';
            });
            if (signaturePads['signatureCanvasAval']) {
                signaturePads['signatureCanvasAval'].clear();
            }
        }
    });

    // Modificar la validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validar firma del cliente
        if (signaturePads['signatureCanvasCliente'].isEmpty()) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La firma del cliente es requerida'
            });
            return;
        }

        // Validar campos y firma del aval si es préstamo con aval
        const tipoPrestamoSelect = document.querySelector('select[name="tipo_prestamo"]');
        if (tipoPrestamoSelect.value === 'aval') {
            // Validar campos del aval
            const camposAval = ['nombre_aval', 'telefono_aval', 'direccion_aval'];
            for (const campo of camposAval) {
                const elemento = document.getElementById(campo);
                if (!elemento.value.trim()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: `El campo ${elemento.previousElementSibling.textContent.replace(' *', '')} es requerido`
                    });
                    return;
                }
            }

            // Validar firma del aval
            if (signaturePads['signatureCanvasAval'].isEmpty()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La firma del aval es requerida'
                });
                return;
            }
        }

        // Agregar firmas al formulario
        const firmaCliente = signaturePads['signatureCanvasCliente'].toDataURL();
        const firmaClienteInput = document.createElement('input');
        firmaClienteInput.type = 'hidden';
        firmaClienteInput.name = 'firma_cliente';
        firmaClienteInput.value = firmaCliente;
        this.appendChild(firmaClienteInput);

        if (tipoPrestamoSelect.value === 'aval') {
            const firmaAval = signaturePads['signatureCanvasAval'].toDataURL();
            const firmaAvalInput = document.createElement('input');
            firmaAvalInput.type = 'hidden';
            firmaAvalInput.name = 'firma_aval';
            firmaAvalInput.value = firmaAval;
            this.appendChild(firmaAvalInput);
        }

        this.submit();
    });
});
</script>

<?php include 'views/footer.php'; ?> 