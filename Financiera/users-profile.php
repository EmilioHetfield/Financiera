<?php
session_start();
include 'config.php';
include 'functions/profile_functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header("Location: pages-login.html");
    exit();
}

// Obtener información del usuario desde la base de datos
try {
    $user_id = $_SESSION['user']['id'];
    $sql = "SELECT id, nombre, usuario, tipo_usuario, telefono FROM usuarios WHERE id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Preparar la información del usuario con las claves correctas
    $user_info = [
        'id' => $result['id'],
        'full_name' => $result['nombre'],
        'email' => $result['usuario'],
        'id_type' => $result['tipo_usuario'],
        'phone' => $result['telefono'],
        'profile_image' => isset($_SESSION['user']['profile_image']) ?
            $_SESSION['user']['profile_image'] :
            'assets/img/profile-img.jpg'
    ];
} catch (PDOException $e) {
    error_log("Error en users-profile.php: " . $e->getMessage());
    $_SESSION['error'] = "Error al cargar el perfil";
    header("Location: index.php");
    exit();
}

// Incluir las vistas
include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';

// Después de incluir nav_menu.php y antes del contenido principal
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

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Perfil</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item">Usuarios</li>
                <li class="breadcrumb-item active">Perfil</li>
            </ol>
        </nav>
    </div>

    <section class="section profile">
        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                        <img src="<?php echo htmlspecialchars($user_info['profile_image']); ?>" alt="Profile" class="rounded-circle">
                        <h2><?php echo htmlspecialchars($user_info['full_name']); ?></h2>
                        <h3><?php echo htmlspecialchars(getTipoUsuario($user_info['id_type'])); ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body pt-3">
                        <ul class="nav nav-tabs nav-tabs-bordered">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Vista General</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Editar Perfil</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Cambiar Contraseña</button>
                            </li>
                        </ul>

                        <div class="tab-content pt-2">
                            <!-- Vista General -->
                            <div class="tab-pane fade show active profile-overview" id="profile-overview">
                                <h5 class="card-title">Detalles del Perfil</h5>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Nombre Completo</div>
                                    <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($user_info['full_name']); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Cargo</div>
                                    <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars(getTipoUsuario($user_info['id_type'])); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Email</div>
                                    <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($user_info['email']); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Teléfono</div>
                                    <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($user_info['phone'] ?? 'No especificado'); ?></div>
                                </div>
                            </div>

                            <!-- Formulario de Edición -->
                            <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
                                <form action="functions/update_profile_image.php" method="POST" enctype="multipart/form-data">
                                    <div class="row mb-3">
                                        <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Imagen de Perfil</label>
                                        <div class="col-md-8 col-lg-9">
                                            <img src="<?php echo htmlspecialchars($user_info['profile_image']); ?>" alt="Profile" class="rounded-circle">
                                            <div class="pt-2">
                                                <input type="file" name="profile_image" class="form-control" id="profileImage" accept="image/*">
                                                <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Actualizar Imagen</button>
                                    </div>
                                </form>
                                <form action="functions/update_profile.php" method="POST">
                                    <div class="row mb-3">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Nombre Completo</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="full_name" type="text" class="form-control" id="fullName"
                                                value="<?php echo htmlspecialchars($user_info['full_name']); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="email" type="email" class="form-control" id="email"
                                                value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="phone" class="col-md-4 col-lg-3 col-form-label">Teléfono</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="phone" type="tel" class="form-control" id="phone"
                                                value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>"
                                                pattern="[0-9]{8,15}" title="Ingrese un número de teléfono válido">
                                            <small class="text-muted">Formato: solo números, 8-15 dígitos</small>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Formulario de Cambio de Contraseña -->
                            <div class="tab-pane fade pt-3" id="profile-change-password">
                                <form id="changePasswordForm">
                                    <div class="row mb-3">
                                        <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Contraseña Actual</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="current_password" type="password" class="form-control" id="currentPassword" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">Nueva Contraseña</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="new_password" type="password" class="form-control" id="newPassword" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Confirmar Nueva Contraseña</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="renew_password" type="password" class="form-control" id="renewPassword" required>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<script>
document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const renewPassword = document.getElementById('renewPassword').value;

    if (newPassword !== renewPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Las contraseñas no coinciden',
        });
        return;
    }

    try {
        const response = await fetch('functions/cambiar_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                current_password: currentPassword,
                nueva_password: newPassword,
                confirmar_password: renewPassword
            })
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Contraseña actualizada exitosamente',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload(); // Recargar la página
            });
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
        });
    }
});
</script>

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<?php include '../Financiera/views/footer.php'; ?>