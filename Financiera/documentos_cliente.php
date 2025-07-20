<?php
require_once 'config.php';
require_once 'functions/session_handler.php';
checkSession();

$cliente_id = $_GET['id'] ?? null;
if (!$cliente_id) {
    header('Location: clientes.php');
    exit;
}

// Obtener información del cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    header('Location: clientes.php');
    exit;
}

include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Documentos del Cliente</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="clientes.php">Clientes</a></li>
                <li class="breadcrumb-item active">Documentos</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            Documentos de <?php echo htmlspecialchars($cliente['nombre_completo']); ?>
                        </h5>

                        <!-- Formulario de carga -->
                        <form id="uploadForm" class="row g-3" enctype="multipart/form-data">
                            <input type="hidden" name="cliente_id" value="<?php echo $cliente_id; ?>">
                            
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Documento</label>
                                <select class="form-select" name="tipo_documento" required>
                                    <option value="">Seleccione...</option>
                                    <option value="ine">INE</option>
                                    <option value="comprobante_domicilio">Comprobante de Domicilio</option>
                                    <option value="foto_cliente">Fotografía del Cliente</option>
                                    <option value="otros">Otros</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Archivo</label>
                                <input type="file" class="form-control" name="documento" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Descripción</label>
                                <input type="text" class="form-control" name="descripcion">
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Subir Documento
                                </button>
                            </div>
                        </form>

                        <hr>

                        <!-- Lista de documentos -->
                        <div class="row" id="documentosContainer">
                            <!-- Los documentos se cargarán aquí -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
// Cargar documentos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDocumentos();
});

function cargarDocumentos() {
    fetch(`functions/obtener_documentos.php?cliente_id=<?php echo $cliente_id; ?>`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('documentosContainer');
            container.innerHTML = data.map(doc => `
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">${doc.tipo_documento}</h6>
                            <p class="card-text small">${doc.descripcion || 'Sin descripción'}</p>
                            <p class="card-text small text-muted">
                                Subido: ${new Date(doc.fecha_subida).toLocaleDateString()}
                            </p>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group w-100">
                                <a href="${doc.ruta}" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="eliminarDocumento(${doc.id})">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        });
}

// Manejar el formulario de carga
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('functions/subir_documento.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Documento subido correctamente'
            });
            
            this.reset();
            cargarDocumentos();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al subir el documento'
        });
    }
});

function eliminarDocumento(documentoId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción eliminará permanentemente el documento",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('documento_id', documentoId);

            fetch('functions/eliminar_documento.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'El documento ha sido eliminado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Recargar la página o actualizar la lista de documentos
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Error al eliminar el documento');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Error al eliminar el documento'
                });
            });
        }
    });
}
</script>

<?php include 'views/footer.php'; ?> 