<?php
session_start();
include 'config.php';
include 'functions/dashboard_queries.php';
require_once 'functions/auth.php';
verificarAcceso(['master', 'vendedor']);

include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Préstamos Registrados</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Préstamos</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Lista de Préstamos</h5>
                        
                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select id="filtroEstadoSolicitud" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="aprobado">Aprobado</option>
                                    <option value="rechazado">Rechazado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" id="filtroFecha" class="form-control" placeholder="Filtrar por fecha">
                            </div>
                            <div class="col-md-3">
                                <button id="btnFiltrar" class="btn btn-primary">
                                    <i class="bi bi-filter"></i> Filtrar
                                </button>
                                <button id="btnLimpiar" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Limpiar
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de Préstamos -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Monto</th>
                                        <th>Plazo</th>
                                        <th>Tasa Interés</th>
                                        <th>Estado</th>
                                        <th>Estado Solicitud</th>
                                        <th>Fecha Solicitud</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaPrestamos">
                                    <!-- Los datos se cargarán dinámicamente -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="text-muted" id="totalRegistros"></p>
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
</main>

<?php include 'views/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let paginaActual = 1;
    const registrosPorPagina = 10;

    // Cargar préstamos inicialmente
    cargarPrestamos();

    // Event Listeners para filtros
    document.getElementById('btnFiltrar').addEventListener('click', () => {
        const filtros = {
            estado_solicitud: document.getElementById('filtroEstadoSolicitud').value,
            fecha: document.getElementById('filtroFecha').value
        };
        paginaActual = 1;
        cargarPrestamos(paginaActual, filtros);
    });

    document.getElementById('btnLimpiar').addEventListener('click', () => {
        document.getElementById('filtroEstadoSolicitud').value = '';
        document.getElementById('filtroFecha').value = '';
        paginaActual = 1;
        cargarPrestamos();
    });

    function cargarPrestamos(pagina = 1, filtros = {}) {
        const data = {
            pagina: pagina,
            registros_por_pagina: registrosPorPagina,
            ...filtros
        };

        fetch('functions/obtener_prestamos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarPrestamos(data.prestamos);
                actualizarPaginacion(data.total_paginas, pagina);
                actualizarTotalRegistros(data.prestamos.length, data.total_registros);
            } else {
                throw new Error(data.message || 'Error al cargar los préstamos');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message
            });
        });
    }

    function mostrarPrestamos(prestamos) {
        const tbody = document.getElementById('tablaPrestamos');
        tbody.innerHTML = '';

        // Debug
        console.log('Préstamos recibidos:', prestamos);

        prestamos.forEach(prestamo => {
            // Debug
            console.log('Procesando préstamo:', prestamo);

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${prestamo.id || ''}</td>
                <td>${prestamo.nombre_cliente || ''}</td>
                <td>${prestamo.monto ? '$' + parseFloat(prestamo.monto).toFixed(2) : ''}</td>
                <td>${prestamo.plazo ? prestamo.plazo + ' meses' : ''}</td>
                <td>${prestamo.tasa_interes ? prestamo.tasa_interes + '%' : ''}</td>
                <td>
                    <span class="badge ${getBadgeClass(prestamo.estado)}">
                        ${prestamo.estado || ''}
                    </span>
                </td>
                <td>
                    <span class="badge ${getBadgeClassSolicitud(prestamo.estado_solicitud)}">
                        ${prestamo.estado_solicitud || ''}
                    </span>
                </td>
                <td>${prestamo.fecha_solicitud ? formatearFecha(prestamo.fecha_solicitud) : ''}</td>
                <td>
                    <a href="ver_prestamo.php?id=${prestamo.id}" class="btn btn-sm btn-info" title="Ver detalles">
                        <i class="bi bi-eye"></i>
                    </a>
                    ${prestamo.estado_solicitud === 'aprobado' ? 
                        `<a href="pagareA.php?prestamo_id=${prestamo.id}" class="btn btn-sm btn-success ms-1" title="Generar Pagaré">
                            <i class="bi bi-file-earmark-text"></i>
                         </a>` : ''}
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Debug
        console.log('Tabla actualizada');
    }

    function actualizarPaginacion(totalPaginas, paginaActual) {
        const paginacion = document.getElementById('paginacion');
        paginacion.innerHTML = '';

        // Botón Anterior
        const btnAnterior = document.createElement('li');
        btnAnterior.className = `page-item ${paginaActual === 1 ? 'disabled' : ''}`;
        btnAnterior.innerHTML = `
            <a class="page-link" href="#" data-pagina="${paginaActual - 1}">
                <i class="bi bi-chevron-left"></i>
            </a>
        `;
        paginacion.appendChild(btnAnterior);

        // Páginas
        for (let i = 1; i <= totalPaginas; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
            li.innerHTML = `
                <a class="page-link" href="#" data-pagina="${i}">${i}</a>
            `;
            paginacion.appendChild(li);
        }

        // Botón Siguiente
        const btnSiguiente = document.createElement('li');
        btnSiguiente.className = `page-item ${paginaActual === totalPaginas ? 'disabled' : ''}`;
        btnSiguiente.innerHTML = `
            <a class="page-link" href="#" data-pagina="${paginaActual + 1}">
                <i class="bi bi-chevron-right"></i>
            </a>
        `;
        paginacion.appendChild(btnSiguiente);

        // Event listener para la paginación
        paginacion.addEventListener('click', (e) => {
            e.preventDefault();
            const link = e.target.closest('a');
            if (link && !link.parentElement.classList.contains('disabled')) {
                const pagina = parseInt(link.dataset.pagina);
                if (pagina && pagina !== paginaActual) {
                    cargarPrestamos(pagina, {
                        estado_solicitud: document.getElementById('filtroEstadoSolicitud').value,
                        fecha: document.getElementById('filtroFecha').value
                    });
                }
            }
        });
    }

    function actualizarTotalRegistros(mostrados, total) {
        document.getElementById('totalRegistros').textContent = 
            `Mostrando ${mostrados} de ${total} registros`;
    }

    function getBadgeClass(estado) {
        if (!estado) return 'bg-secondary';
        return estado === 'Completado' ? 'bg-success' : 'bg-warning';
    }

    function getBadgeClassSolicitud(estado) {
        if (!estado) return 'bg-secondary';
        const clases = {
            'pendiente': 'bg-warning',
            'aprobado': 'bg-success',
            'rechazado': 'bg-danger'
        };
        return clases[estado] || 'bg-secondary';
    }

    function formatearFecha(fecha) {
        try {
            return new Date(fecha).toLocaleDateString('es-MX', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            console.error('Error al formatear fecha:', error);
            return fecha;
        }
    }
});

// Funciones globales para acciones
window.verPrestamo = function(id) {
    window.location.href = `ver_prestamo.php?id=${id}`;
}

window.generarPagare = function(id) {
    window.location.href = `pagareA.php?prestamo_id=${id}`;
}
</script> 