<?php
session_start();
include 'config.php';
include 'functions/dashboard_queries.php';
require_once 'functions/auth.php';
verificarAcceso(['master', 'autorizador']);

// Incluir vistas después de verificar acceso
include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Autorización de Préstamos</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Autorizar Préstamos</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Préstamos Pendientes de Autorización</h5>
                        
                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="filtroCliente" placeholder="Buscar por cliente">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filtroVendedor">
                                    <option value="">Todos los vendedores</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control" id="filtroFecha">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary" onclick="aplicarFiltros()">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                <button class="btn btn-secondary" onclick="limpiarFiltros()">
                                    <i class="bi bi-x-circle"></i> Limpiar
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de préstamos -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Vendedor</th>
                                        <th>Monto</th>
                                        <th>Plazo</th>
                                        <th>Fecha Solicitud</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaPrestamos">
                                    <!-- Los préstamos se cargarán aquí dinámicamente -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p id="totalRegistros">Mostrando 0 registros</p>
                            </div>
                            <div class="col-md-6">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-end" id="paginacion"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de Autorización -->
    <div class="modal fade" id="modalAutorizacion" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Autorizar Préstamo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAutorizacion">
                        <input type="hidden" id="prestamo_id">
                        
                        <!-- Información del préstamo -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Cliente</label>
                                <input type="text" class="form-control" id="nombre_cliente" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Monto Solicitado</label>
                                <input type="text" class="form-control" id="monto_solicitado" readonly>
                            </div>
                        </div>

                        <!-- Campos de autorización -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Monto Autorizado *</label>
                                <input type="number" class="form-control" id="monto_autorizado" required step="0.01" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Plazo (meses) *</label>
                                <select class="form-select" id="plazo" required>
                                    <option value="">Seleccione el plazo</option>
                                    <option value="3">3 meses</option>
                                    <option value="6">6 meses</option>
                                    <option value="12">12 meses</option>
                                    <option value="18">18 meses</option>
                                    <option value="24">24 meses</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Frecuencia de Pago *</label>
                                <select class="form-select" id="frecuencia_pago" required>
                                    <option value="semanal">Semanal</option>
                                    <option value="quincenal">Quincenal</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tasa de Interés (%) *</label>
                                <input type="number" class="form-control" id="tasa_interes" required step="0.01" min="0" max="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Primer Pago *</label>
                                <input type="date" class="form-control" id="fecha_primer_pago" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Pago por Periodo</label>
                                <input type="text" class="form-control" id="pago_periodo" readonly>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Total a Pagar:</strong> <span id="total_pagar">$0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Interés Total:</strong> <span id="interes_total">$0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total de Pagos:</strong> <span id="total_pagos">0</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Último Pago:</strong> <span id="fecha_ultimo_pago">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="rechazarPrestamo()">Rechazar</button>
                    <button type="button" class="btn btn-success" onclick="autorizarPrestamo()">Autorizar</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Variables globales
let paginaActual = 1;
const registrosPorPagina = 10;

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarPrestamos();
    cargarVendedores();
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('fecha_primer_pago').min = tomorrow.toISOString().split('T')[0];

    // Agregar event listeners para los campos que disparan el cálculo
    const camposCalculo = ['monto_autorizado', 'plazo', 'tasa_interes', 'frecuencia_pago', 'fecha_primer_pago'];
    
    camposCalculo.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.addEventListener('change', recalcular);
            elemento.addEventListener('input', recalcular);
        }
    });
});

// Función para cargar préstamos
function cargarPrestamos(pagina = 1) {
    const filtros = {
        cliente: document.getElementById('filtroCliente').value,
        vendedor: document.getElementById('filtroVendedor').value,
        fecha: document.getElementById('filtroFecha').value,
        pagina: pagina,
        registros_por_pagina: registrosPorPagina
    };

    fetch('functions/obtener_prestamos_pendientes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(filtros)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarPrestamos(data.prestamos);
            actualizarPaginacion(data.total_paginas, data.pagina_actual);
            document.getElementById('totalRegistros').textContent = 
                `Mostrando ${data.prestamos.length} de ${data.total_registros} registros`;
        } else {
            alert('Error al cargar los préstamos: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para mostrar préstamos en la tabla
function mostrarPrestamos(prestamos) {
    const tbody = document.getElementById('tablaPrestamos');
    tbody.innerHTML = '';

    prestamos.forEach(prestamo => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${prestamo.id}</td>
            <td>${prestamo.nombre_cliente}</td>
            <td>${prestamo.nombre_vendedor}</td>
            <td>$${parseFloat(prestamo.monto).toFixed(2)}</td>
            <td>${prestamo.plazo} meses</td>
            <td>${formatearFecha(prestamo.fecha_solicitud)}</td>
            <td>
                <span class="badge bg-warning">Pendiente</span>
            </td>
            <td>
                <button class="btn btn-sm btn-success" onclick="abrirModalAutorizacion(${prestamo.id})">
                    <i class="bi bi-check-circle"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Función para abrir el modal de autorización
function abrirModalAutorizacion(prestamoId) {
    // Obtener detalles del préstamo
    fetch('functions/obtener_detalle_prestamo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            prestamo_id: prestamoId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const prestamo = data.prestamo;
            
            // Llenar el formulario con los datos
            document.getElementById('prestamo_id').value = prestamo.id;
            document.getElementById('nombre_cliente').value = prestamo.nombre_cliente;
            document.getElementById('monto_solicitado').value = formatMoney(prestamo.monto);
            
            // Limpiar campos de autorización
            document.getElementById('monto_autorizado').value = prestamo.monto;
            document.getElementById('plazo').value = '';
            document.getElementById('tasa_interes').value = '';
            document.getElementById('observaciones').value = '';
            
            // Limpiar cálculos
            document.getElementById('pago_periodo').value = '';
            document.getElementById('total_pagar').textContent = '$0.00';
            document.getElementById('interes_total').textContent = '$0.00';
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('modalAutorizacion'));
            modal.show();
        } else {
            alert('Error al obtener detalles del préstamo: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función auxiliar para formatear montos
function formatMoney(amount) {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Función para calcular el pago semanal y totales
function calcularPagos() {
    const montoAutorizado = parseFloat(document.getElementById('monto_autorizado').value) || 0;
    const plazoMeses = parseInt(document.getElementById('plazo').value) || 0;
    const tasaInteres = parseFloat(document.getElementById('tasa_interes').value) || 0;
    const frecuenciaPago = document.getElementById('frecuencia_pago').value;
    const fechaPrimerPago = document.getElementById('fecha_primer_pago').value;

    if (montoAutorizado > 0 && plazoMeses > 0 && tasaInteres > 0 && fechaPrimerPago) {
        const interes = (montoAutorizado * (tasaInteres / 100));
        const totalPagar = montoAutorizado + interes;
        const semanasTotales = plazoMeses * 4; // 4 semanas por mes

        let pagoPeriodo;
        let totalPagos;
        let diasEntrePagos;

        if (frecuenciaPago === 'quincenal') {
            pagoPeriodo = totalPagar / (semanasTotales / 2);
            totalPagos = semanasTotales / 2;
            diasEntrePagos = 15;
        } else {
            pagoPeriodo = totalPagar / semanasTotales;
            totalPagos = semanasTotales;
            diasEntrePagos = 7;
        }

        // Calcular fecha último pago
        const fechaInicio = new Date(fechaPrimerPago);
        const fechaUltimoPago = new Date(fechaInicio);
        fechaUltimoPago.setDate(fechaInicio.getDate() + (diasEntrePagos * (totalPagos - 1)));

        document.getElementById('pago_periodo').value = '$' + pagoPeriodo.toFixed(2);
        document.getElementById('total_pagar').textContent = '$' + totalPagar.toFixed(2);
        document.getElementById('interes_total').textContent = '$' + interes.toFixed(2);
        document.getElementById('total_pagos').textContent = totalPagos + 
            (frecuenciaPago === 'quincenal' ? ' pagos quincenales' : ' pagos semanales');
        document.getElementById('fecha_ultimo_pago').textContent = 
            fechaUltimoPago.toLocaleDateString('es-MX');
    }
}

// Event listeners para recalcular
['monto_autorizado', 'plazo', 'tasa_interes', 'frecuencia_pago', 'fecha_primer_pago'].forEach(id => {
    document.getElementById(id).addEventListener('input', calcularPagos);
    document.getElementById(id).addEventListener('change', calcularPagos);
});

// Función para autorizar préstamo
function autorizarPrestamo() {
    // Obtener todos los valores necesarios
    const montoAutorizado = parseFloat(document.getElementById('monto_autorizado').value);
    const plazoMeses = parseInt(document.getElementById('plazo').value);
    const tasaInteres = parseFloat(document.getElementById('tasa_interes').value);
    const frecuenciaPago = document.getElementById('frecuencia_pago').value;
    const fechaPrimerPago = document.getElementById('fecha_primer_pago').value;
    
    // Calcular fecha último pago
    const semanasTotales = plazoMeses * 4;
    const numeroPagos = frecuenciaPago === 'quincenal' ? Math.ceil(semanasTotales / 2) : semanasTotales;
    const diasEntrePagos = frecuenciaPago === 'quincenal' ? 15 : 7;
    
    const fechaInicio = new Date(fechaPrimerPago);
    const fechaUltimoPago = new Date(fechaInicio);
    fechaUltimoPago.setDate(fechaInicio.getDate() + (diasEntrePagos * (numeroPagos - 1)));

    const formData = {
        prestamo_id: document.getElementById('prestamo_id').value,
        monto_autorizado: montoAutorizado,
        plazo: plazoMeses,
        plazo_semanas: semanasTotales,
        tasa_interes: tasaInteres,
        frecuencia_pago: frecuenciaPago,
        fecha_primer_pago: fechaPrimerPago,
        fecha_ultimo_pago: fechaUltimoPago.toISOString().split('T')[0],
        observaciones: document.getElementById('observaciones').value,
        decision: 'aprobado'
    };

    if (!validarFormulario(formData)) {
        return;
    }

    // Enviar datos al servidor
    fetch('functions/autorizar_prestamo.php', {
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
                text: 'Préstamo autorizado exitosamente',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAutorizacion'));
                modal.hide();
                cargarPrestamos();
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al procesar la solicitud',
        });
    });
}

// Función para rechazar préstamo
function rechazarPrestamo() {
    // Similar lógica para rechazar el préstamo
    // Usar SweetAlert2 para mostrar mensajes
}

function validarFormulario(data) {
    if (!data.monto_autorizado || data.monto_autorizado <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Validación',
            text: 'Por favor ingrese un monto autorizado válido'
        });
        return false;
    }
    if (!data.plazo) {
        Swal.fire({
            icon: 'warning',
            title: 'Validación',
            text: 'Por favor seleccione un plazo'
        });
        return false;
    }
    if (!data.tasa_interes || data.tasa_interes <= 0 || data.tasa_interes > 100) {
        Swal.fire({
            icon: 'warning',
            title: 'Validación',
            text: 'Por favor ingrese una tasa de interés válida (entre 0 y 100)'
        });
        return false;
    }
    if (!data.frecuencia_pago) {
        Swal.fire({
            icon: 'warning',
            title: 'Validación',
            text: 'Por favor seleccione la frecuencia de pago'
        });
        return false;
    }
    if (!data.fecha_primer_pago) {
        Swal.fire({
            icon: 'warning',
            title: 'Validación',
            text: 'Por favor seleccione la fecha del primer pago'
        });
        return false;
    }
    return true;
}

// Funciones auxiliares
function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function actualizarPaginacion(totalPaginas, paginaActual) {
    const paginacion = document.getElementById('paginacion');
    paginacion.innerHTML = '';

    // Botón anterior
    const btnAnterior = document.createElement('li');
    btnAnterior.className = `page-item ${paginaActual === 1 ? 'disabled' : ''}`;
    btnAnterior.innerHTML = `
        <a class="page-link" href="#" onclick="cargarPrestamos(${paginaActual - 1})">
            Anterior
        </a>
    `;
    paginacion.appendChild(btnAnterior);

    // Páginas
    for (let i = 1; i <= totalPaginas; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
        li.innerHTML = `
            <a class="page-link" href="#" onclick="cargarPrestamos(${i})">
                ${i}
            </a>
        `;
        paginacion.appendChild(li);
    }

    // Botón siguiente
    const btnSiguiente = document.createElement('li');
    btnSiguiente.className = `page-item ${paginaActual === totalPaginas ? 'disabled' : ''}`;
    btnSiguiente.innerHTML = `
        <a class="page-link" href="#" onclick="cargarPrestamos(${paginaActual + 1})">
            Siguiente
        </a>
    `;
    paginacion.appendChild(btnSiguiente);
}

// Funciones de filtrado
function aplicarFiltros() {
    paginaActual = 1;
    cargarPrestamos(1);
}

function limpiarFiltros() {
    document.getElementById('filtroCliente').value = '';
    document.getElementById('filtroVendedor').value = '';
    document.getElementById('filtroFecha').value = '';
    aplicarFiltros();
}

function cargarVendedores() {
    fetch('functions/obtener_vendedores.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('filtroVendedor');
                data.vendedores.forEach(vendedor => {
                    const option = document.createElement('option');
                    option.value = vendedor.id;
                    option.textContent = vendedor.nombre;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

function recalcular() {
    console.log('Iniciando recálculo...'); // Debug
    
    // Obtener valores
    const montoAutorizado = parseFloat(document.getElementById('monto_autorizado').value) || 0;
    const plazoMeses = parseInt(document.getElementById('plazo').value) || 0;
    const tasaInteres = parseFloat(document.getElementById('tasa_interes').value) || 0;
    const frecuenciaPago = document.getElementById('frecuencia_pago').value;
    const fechaPrimerPago = document.getElementById('fecha_primer_pago').value;

    console.log('Valores capturados:', {
        montoAutorizado,
        plazoMeses,
        tasaInteres,
        frecuenciaPago,
        fechaPrimerPago
    });

    // Validar que tengamos todos los valores necesarios
    if (montoAutorizado > 0 && plazoMeses > 0 && tasaInteres > 0) {
        // Cálculos básicos
        const interes = (montoAutorizado * (tasaInteres / 100));
        const totalPagar = montoAutorizado + interes;
        const semanasTotales = plazoMeses * 4;

        // Cálculos según frecuencia
        let numeroPagos;
        let pagoPeriodo;

        if (frecuenciaPago === 'quincenal') {
            numeroPagos = Math.ceil(semanasTotales / 2);
            pagoPeriodo = totalPagar / numeroPagos;
        } else {
            numeroPagos = semanasTotales;
            pagoPeriodo = totalPagar / numeroPagos;
        }

        // Calcular fecha último pago
        let fechaUltimoPago = '-';
        if (fechaPrimerPago) {
            const fecha = new Date(fechaPrimerPago);
            const diasPorPago = frecuenciaPago === 'quincenal' ? 15 : 7;
            fecha.setDate(fecha.getDate() + (diasPorPago * (numeroPagos - 1)));
            fechaUltimoPago = fecha.toLocaleDateString('es-MX');
        }

        // Actualizar la interfaz
        document.getElementById('pago_periodo').value = formatMoney(pagoPeriodo);
        document.getElementById('total_pagar').textContent = formatMoney(totalPagar);
        document.getElementById('interes_total').textContent = formatMoney(interes);
        document.getElementById('total_pagos').textContent = `${numeroPagos} pagos ${frecuenciaPago}es`;
        document.getElementById('fecha_ultimo_pago').textContent = fechaUltimoPago;

        console.log('Cálculos completados:', {
            pagoPeriodo,
            totalPagar,
            interes,
            numeroPagos,
            fechaUltimoPago
        });
    }
}
</script>

<?php include 'views/footer.php'; ?> 