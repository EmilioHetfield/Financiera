<?php
ob_start();
require_once 'functions/session_handler.php';
checkSession();
require_once 'config.php';

try {
    $vendedor_id = $_SESSION['user']['id'];
    error_log("ID del vendedor actual: " . $vendedor_id);

    // Consulta modificada para mostrar solo préstamos sin pagarés
    $sql = "SELECT 
        p.id as prestamo_id,
        p.cliente_id,
        p.monto_autorizado,
        p.monto_total,
        p.plazo_semanas,
        p.tasa_interes,
        p.estado,
        p.estado_solicitud,
        p.frecuencia_pago,
        c.nombre_completo,
        c.id_vendedor
    FROM prestamos p
    INNER JOIN clientes c ON p.cliente_id = c.id
    WHERE c.id_vendedor = :vendedor_id
    AND p.estado_solicitud = 'aprobado'
    AND p.estado != 'Completado'
    AND NOT EXISTS (
        SELECT 1 
        FROM pagares pg 
        WHERE pg.prestamo_id = p.id
    )
    ORDER BY p.id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':vendedor_id', $vendedor_id, PDO::PARAM_INT);
    $stmt->execute();
    $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug de resultados
    error_log("Número de préstamos encontrados: " . count($prestamos));
    if (count($prestamos) > 0) {
        error_log("Primer préstamo encontrado: " . print_r($prestamos[0], true));
    } else {
        error_log("No se encontraron préstamos sin pagarés para el vendedor ID: " . $vendedor_id);
    }

} catch (PDOException $e) {
    error_log("Error en la consulta: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    $prestamos = [];
}

// Inicializar $prestamos como array vacío si no está definido
if (!isset($prestamos)) {
    $prestamos = [];
}

// Incluir las vistas
include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Generar Pagaré</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Generar Pagaré</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Formulario de Pagaré</h5>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo $_SESSION['error'];
                                unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form id="pagareForm" class="needs-validation" novalidate>
                            <!-- Tipo de Pagaré -->
                            <div class="mb-3">
                                <label class="form-label">Tipo de Pagaré *</label>
                                <select class="form-select" id="tipo_pagare" name="tipo_pagare" required>
                                    <option value="">Seleccione el tipo de pagaré...</option>
                                    <option value="normal">Normal</option>
                                    <option value="con_aval">Con Aval</option>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione el tipo de pagaré</div>
                            </div>

                            <!-- Selección de Préstamo -->
                            <div class="mb-3">
                                <label for="prestamo_id" class="form-label">Seleccionar Préstamo *</label>
                                <select class="form-select" id="prestamo_id" name="prestamo_id" required>
                                    <option value="">Seleccione un préstamo...</option>
                                    <?php foreach ($prestamos as $prestamo): ?>
                                        <?php
                                        // Debug de los datos
                                        error_log("Datos del préstamo: " . print_r($prestamo, true));
                                        ?>
                                        <option value="<?php echo htmlspecialchars($prestamo['prestamo_id']); ?>"
                                            data-cliente="<?php echo htmlspecialchars($prestamo['nombre_completo']); ?>"
                                            data-monto="<?php echo htmlspecialchars($prestamo['monto_autorizado']); ?>"
                                            data-plazo_semanas="<?php echo htmlspecialchars($prestamo['plazo_semanas']); ?>"
                                            data-tasa="<?php echo htmlspecialchars($prestamo['tasa_interes']); ?>">
                                            <?php
                                            echo htmlspecialchars(
                                                $prestamo['nombre_completo'] .
                                                    ' - $' . number_format($prestamo['monto_autorizado'], 2) .
                                                    ' - Plazo: ' . $prestamo['plazo_semanas'] . ' semanas'
                                            );
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un préstamo</div>
                            </div>

                            <!-- Datos del Cliente -->
                            <div class="mb-3">
                                <label for="nombre_cliente" class="form-label">Nombre del Cliente *</label>
                                <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" readonly required>
                            </div>

                            <div class="mb-3">
                                <label for="monto" class="form-label">Monto Autorizado *</label>
                                <input type="number" class="form-control" id="monto" name="monto" step="0.01" readonly required>
                            </div>

                            <!-- Fechas -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha" class="form-label">Fecha de Emisión *</label>
                                        <input type="date" class="form-control" id="fecha" name="fecha"
                                            value="<?php echo date('Y-m-d'); ?>" required>
                                        <div class="invalid-feedback">Por favor seleccione la fecha de emisión</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_limite_pago" class="form-label">Fecha Límite de Pago *</label>
                                        <input type="date" class="form-control" id="fecha_limite_pago"
                                            name="fecha_limite_pago" required>
                                        <div class="invalid-feedback">Por favor seleccione la fecha límite</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección del Aval (inicialmente oculta) -->
                            <div id="seccionAval" style="display: none;">
                                <h5 class="mb-3">Información del Aval</h5>
                                <!-- Nombre del Aval -->
                                <div class="mb-3">
                                    <label for="nombre_aval" class="form-label">Nombre del Aval *</label>
                                    <input type="text" class="form-control" id="nombre_aval" name="nombre_aval" 
                                           placeholder="Nombre completo del aval">
                                    <div class="invalid-feedback">Por favor ingrese el nombre del aval</div>
                                </div>

                                <!-- Dirección del Aval -->
                                <div class="mb-3">
                                    <label for="direccion_aval" class="form-label">Dirección del Aval *</label>
                                    <input type="text" class="form-control" id="direccion_aval" name="direccion_aval" 
                                           placeholder="Dirección completa del aval">
                                    <div class="invalid-feedback">Por favor ingrese la dirección del aval</div>
                                </div>

                                <!-- Teléfono del Aval -->
                                <div class="mb-3">
                                    <label for="telefono_aval" class="form-label">Teléfono del Aval *</label>
                                    <input type="tel" class="form-control" id="telefono_aval" name="telefono_aval" 
                                           placeholder="Número de teléfono del aval">
                                    <div class="invalid-feedback">Por favor ingrese el teléfono del aval</div>
                                </div>

                                <!-- Firma del Aval -->
                                <div class="mb-4">
                                    <label class="form-label">Firma del Aval *</label>
                                    <div class="signature-container">
                                        <canvas id="signatureCanvasAval"></canvas>
                                    </div>
                                    <div class="firma-buttons mt-2">
                                        <button type="button" class="btn btn-secondary" onclick="limpiarFirma('signatureCanvasAval')">
                                            <i class="bi bi-eraser"></i> Limpiar Firma Aval
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Firma del Cliente -->
                            <div class="mb-4">
                                <label class="form-label">Firma del Cliente *</label>
                                <div class="signature-container">
                                    <canvas id="signatureCanvasCliente"></canvas>
                                </div>
                                <div class="firma-buttons">
                                    <button type="button" class="btn btn-secondary"
                                        onclick="limpiarFirma('signatureCanvasCliente')">
                                        <i class="bi bi-eraser"></i> Limpiar Firma Cliente
                                    </button>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary" id="btnSubmit">
                                    <i class="bi bi-save"></i> Guardar Pagaré
                                </button>
                                <a href="prestamos.php" class="btn btn-secondary ms-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'views/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Objeto para almacenar las instancias de los canvas
    let signaturePads = {};

    // Función para configurar el canvas
    function setupCanvas(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error('Canvas no encontrado:', canvasId);
            return;
        }

        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        // Función para ajustar el tamaño del canvas
        function resizeCanvas() {
            const container = canvas.parentElement;
            const rect = container.getBoundingClientRect();
            
            // Establecer el tamaño del canvas al tamaño del contenedor
            canvas.width = rect.width;
            canvas.height = rect.height;
            
            // Restablecer el contexto después del redimensionamiento
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000000';
        }

        // Funciones de dibujo
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

        // Función para obtener coordenadas según el tipo de evento
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

        // Configurar eventos
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Eventos táctiles
        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            startDrawing(e);
        });
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        // Ajustar tamaño inicial y en redimensionamiento
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        // Guardar la referencia
        signaturePads[canvasId] = {
            canvas: canvas,
            ctx: ctx,
            clear: function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        };
    }

    // Función global para limpiar firma
    window.limpiarFirma = function(canvasId) {
        if (signaturePads[canvasId]) {
            signaturePads[canvasId].clear();
        }
    };

    // Inicializar el canvas del cliente inmediatamente
    setupCanvas('signatureCanvasCliente');

    // Manejar el cambio en el tipo de pagaré
    document.getElementById('tipo_pagare').addEventListener('change', function() {
        const seccionAval = document.getElementById('seccionAval');
        const camposAval = ['nombre_aval', 'direccion_aval', 'telefono_aval'];
        
        if (this.value === 'con_aval') {
            seccionAval.style.display = 'block';
            // Hacer campos obligatorios
            camposAval.forEach(campo => {
                const elemento = document.getElementById(campo);
                if (elemento) {
                    elemento.required = true;
                }
            });
            // Inicializar el canvas del aval
            setTimeout(() => {
                setupCanvas('signatureCanvasAval');
            }, 100);
        } else {
            seccionAval.style.display = 'none';
            // Quitar obligatoriedad y limpiar campos
            camposAval.forEach(campo => {
                const elemento = document.getElementById(campo);
                if (elemento) {
                    elemento.required = false;
                    elemento.value = '';
                }
            });
            if (signaturePads['signatureCanvasAval']) {
                signaturePads['signatureCanvasAval'].clear();
            }
        }
    });

    // Agregar esta función al inicio del script
    async function verificarPagareExistente(prestamoId) {
        try {
            const response = await fetch('functions/verificar_pagare.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ prestamo_id: prestamoId })
            });
            const data = await response.json();
            return data.existe;
        } catch (error) {
            console.error('Error al verificar pagaré:', error);
            return false;
        }
    }

    // Modificar el event listener del prestamo_id
    document.getElementById('prestamo_id').addEventListener('change', async function() {
        // Agregar console.log para depuración
        console.log('Préstamo seleccionado:', this.value);
        
        if (this.value) {
            const option = this.options[this.selectedIndex];
            
            // Depurar los datos del dataset
            console.log('Datos del préstamo seleccionado:', {
                cliente: option.dataset.cliente,
                monto: option.dataset.monto,
                plazo_semanas: option.dataset.plazo_semanas,
                tasa: option.dataset.tasa
            });

            // Actualizar los campos
            const nombreClienteInput = document.getElementById('nombre_cliente');
            const montoInput = document.getElementById('monto');
            const fechaLimitePagoInput = document.getElementById('fecha_limite_pago');
            
            if (nombreClienteInput && montoInput) {
                nombreClienteInput.value = option.dataset.cliente || '';
                montoInput.value = option.dataset.monto || '';
                
                // Calcular fecha límite
                const plazoSemanas = parseInt(option.dataset.plazo_semanas);
                if (!isNaN(plazoSemanas)) {
                    const fechaEmision = new Date(document.getElementById('fecha').value);
                    const fechaLimite = new Date(fechaEmision);
                    fechaLimite.setDate(fechaLimite.getDate() + (plazoSemanas * 7));
                    
                    // Formatear fecha para el input
                    const fechaFormateada = fechaLimite.toISOString().split('T')[0];
                    fechaLimitePagoInput.value = fechaFormateada;
                    
                    console.log('Fecha límite calculada:', fechaFormateada);
                }
            } else {
                console.error('No se encontraron los elementos del formulario');
            }

            // Verificar pagaré existente
            try {
                const existePagare = await verificarPagareExistente(this.value);
                if (existePagare) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ya existe un pagaré para este préstamo'
                    });
                    this.value = '';
                    nombreClienteInput.value = '';
                    montoInput.value = '';
                    fechaLimitePagoInput.value = '';
                }
            } catch (error) {
                console.error('Error al verificar pagaré:', error);
            }
        } else {
            // Limpiar campos si no hay selección
            document.getElementById('nombre_cliente').value = '';
            document.getElementById('monto').value = '';
            document.getElementById('fecha_limite_pago').value = '';
        }
    });

    // Asegurarse que el evento change se dispare al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const prestamoSelect = document.getElementById('prestamo_id');
        if (prestamoSelect.value) {
            prestamoSelect.dispatchEvent(new Event('change'));
        }
    });

    // Manejar envío del formulario
    document.getElementById('pagareForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validar firma del cliente
        const canvasCliente = document.getElementById('signatureCanvasCliente');
        const ctxCliente = canvasCliente.getContext('2d');
        const pixelsCliente = ctxCliente.getImageData(0, 0, canvasCliente.width, canvasCliente.height).data;
        const hasSignatureCliente = pixelsCliente.some(pixel => pixel !== 0);

        if (!hasSignatureCliente) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La firma del cliente es requerida'
            });
            return;
        }

        // Validar firma del aval si es necesario
        if (document.getElementById('tipo_pagare').value === 'con_aval') {
            const camposAval = ['nombre_aval', 'direccion_aval', 'telefono_aval'];
            for (const campo of camposAval) {
                if (!document.getElementById(campo).value.trim()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: `El campo ${document.getElementById(campo).previousElementSibling.textContent.replace(' *', '')} es requerido`
                    });
                    return;
                }
            }
        }

        const btnSubmit = document.getElementById('btnSubmit');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

        try {
            const firmaCliente = canvasCliente.toDataURL();
            
            const datos = {
                prestamo_id: document.getElementById('prestamo_id').value,
                tipo_pagare: document.getElementById('tipo_pagare').value,
                nombre_cliente: document.getElementById('nombre_cliente').value,
                monto: document.getElementById('monto').value,
                fecha: document.getElementById('fecha').value,
                fecha_limite_pago: document.getElementById('fecha_limite_pago').value,
                firma_cliente: firmaCliente
            };

            if (datos.tipo_pagare === 'con_aval') {
                datos.nombre_aval = document.getElementById('nombre_aval').value;
                datos.direccion_aval = document.getElementById('direccion_aval').value;
                datos.telefono_aval = document.getElementById('telefono_aval').value;
                datos.firma_aval = document.getElementById('signatureCanvasAval').toDataURL();
            }

            const response = await fetch('functions/procesar_pagare.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datos)
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Pagaré guardado correctamente',
                    showConfirmButton: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'index.php';
                    }
                });
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Error al procesar la solicitud'
            });
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-save"></i> Guardar Pagaré';
        }
    });
});
</script>

</body>

</html>