<?php
session_start();
require_once 'functions/auth.php';
include 'config.php';
verificarAcceso(['master']);

include '../Financiera/views/head.php';
include '../Financiera/views/header.php';
include '../Financiera/views/nav_menu.php';

if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['success'];
        unset($_SESSION['success']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Usuarios</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Registrar Usuario</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Registro de Nuevo Usuario</h5>

                        <form action="functions/insert_usuario.php" method="post" class="needs-validation" novalidate>
                            <!-- Datos Personales -->
                            <div class="row mb-3">
                                <label for="nombre" class="col-sm-3 col-form-label">Nombre Completo</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                    <div class="invalid-feedback">Por favor ingrese el nombre completo.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="usuario" class="col-sm-3 col-form-label">Usuario/Email</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" id="usuario" name="usuario" required>
                                    <div class="invalid-feedback">Por favor ingrese un email válido.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="telefono" class="col-sm-3 col-form-label">Teléfono</label>
                                <div class="col-sm-9">
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           pattern="[0-9]{8,15}" title="Ingrese un número de teléfono válido">
                                    <small class="text-muted">Formato: solo números, 8-15 dígitos</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="password" class="col-sm-3 col-form-label">Contraseña</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="invalid-feedback">Por favor ingrese una contraseña.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="confirm_password" class="col-sm-3 col-form-label">Confirmar Contraseña</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tipo_usuario" class="col-sm-3 col-form-label">Tipo de Usuario</label>
                                <div class="col-sm-9">
                                    <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                        <option value="">Seleccione un tipo</option>
                                        <option value="vendedor">Vendedor</option>
                                        <option value="autorizador">Autorizador</option>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione un tipo de usuario.</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-9 offset-sm-3">
                                    <button type="submit" class="btn btn-primary">Registrar Usuario</button>
                                    <button type="reset" class="btn btn-secondary">Limpiar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
// Validación del formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.needs-validation');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            event.preventDefault();
            event.stopPropagation();
        } else {
            confirmPassword.setCustomValidity('');
        }

        form.classList.add('was-validated');
    }, false);

    confirmPassword.addEventListener('input', function() {
        if (password.value === confirmPassword.value) {
            confirmPassword.setCustomValidity('');
        } else {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
        }
    });
});
</script>

<?php include '../Financiera/views/footer.php'; ?>