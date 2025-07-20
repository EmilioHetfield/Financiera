<?php
session_start();
include 'config.php';
require_once 'functions/auth.php';
verificarAcceso(['master']); // Solo usuarios master pueden acceder

include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Gestión de Contraseñas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Cambiar Contraseñas</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Cambiar Contraseñas de Trabajadores</h5>

                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos los tipos</option>
                                    <option value="vendedor">Vendedores</option>
                                    <option value="autorizador">Autorizadores</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="busquedaNombre" placeholder="Buscar por nombre...">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-primary" onclick="buscarUsuarios()">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de Usuarios -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Usuario</th>
                                        <th>Tipo</th>
                                        <th>Última Actualización</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaUsuarios">
                                    <!-- Los usuarios se cargarán aquí dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="modalPassword" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formPassword">
                        <input type="hidden" id="usuario_id">
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="nombre_usuario" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="nueva_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirmar_password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="cambiarPassword()">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    buscarUsuarios();
});

function buscarUsuarios() {
    const tipo = document.getElementById('filtroTipo').value;
    const nombre = document.getElementById('busquedaNombre').value;

    fetch('functions/obtener_usuarios.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            tipo: tipo,
            nombre: nombre
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarUsuarios(data.usuarios);
        } else {
            alert('Error al obtener usuarios: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function mostrarUsuarios(usuarios) {
    const tbody = document.getElementById('tablaUsuarios');
    tbody.innerHTML = '';

    usuarios.forEach(usuario => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${usuario.id}</td>
            <td>${usuario.nombre}</td>
            <td>${usuario.usuario}</td>
            <td>
                <span class="badge bg-${usuario.tipo_usuario === 'vendedor' ? 'success' : 'primary'}">
                    ${usuario.tipo_usuario}
                </span>
            </td>
            <td>${usuario.ultima_actualizacion || 'N/A'}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="abrirModalPassword(${usuario.id}, '${usuario.nombre}')">
                    <i class="bi bi-key"></i> Cambiar Contraseña
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function abrirModalPassword(id, nombre) {
    document.getElementById('usuario_id').value = id;
    document.getElementById('nombre_usuario').value = nombre;
    document.getElementById('nueva_password').value = '';
    document.getElementById('confirmar_password').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('modalPassword'));
    modal.show();
}

function cambiarPassword() {
    const id = document.getElementById('usuario_id').value;
    const nueva = document.getElementById('nueva_password').value;
    const confirmar = document.getElementById('confirmar_password').value;

    if (nueva !== confirmar) {
        alert('Las contraseñas no coinciden');
        return;
    }

    if (nueva.length < 6) {
        alert('La contraseña debe tener al menos 6 caracteres');
        return;
    }

    fetch('functions/cambiar_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            usuario_id: id,
            nueva_password: nueva
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Contraseña actualizada exitosamente');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalPassword'));
            modal.hide();
            buscarUsuarios();
        } else {
            alert('Error al actualizar contraseña: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

<?php include 'views/footer.php'; ?> 