<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

// Verificar acceso
verificarAcceso(['master']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener y limpiar datos del formulario
        $nombre = trim($_POST['nombre']);
        $usuario = trim($_POST['usuario']);
        $telefono = trim($_POST['telefono']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $tipo_usuario = $_POST['tipo_usuario'];

        // Validaciones
        $errores = [];

        // Validar campos requeridos
        if (empty($nombre)) $errores[] = "El nombre es requerido";
        if (empty($usuario)) $errores[] = "El email es requerido";
        if (empty($password)) $errores[] = "La contraseña es requerida";
        if (empty($tipo_usuario)) $errores[] = "El tipo de usuario es requerido";

        // Validar email
        if (!filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El formato del email no es válido";
        }

        // Validar que las contraseñas coincidan
        if ($password !== $confirm_password) {
            $errores[] = "Las contraseñas no coinciden";
        }

        // Validar longitud de la contraseña
        if (strlen($password) < 6) {
            $errores[] = "La contraseña debe tener al menos 6 caracteres";
        }

        // Validar tipo de usuario
        $tipos_permitidos = ['master', 'vendedor', 'autorizador'];
        if (!in_array($tipo_usuario, $tipos_permitidos)) {
            $errores[] = "Tipo de usuario no válido";
        }

        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        if ($stmt->rowCount() > 0) {
            $errores[] = "El email ya está registrado";
        }

        // Si hay errores, redirigir con mensajes
        if (!empty($errores)) {
            $_SESSION['error'] = implode("<br>", $errores);
            header('Location: ../registrousuario.php');
            exit();
        }

        // Hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Preparar la consulta SQL
        $sql = "INSERT INTO usuarios (nombre, usuario, password, tipo_usuario, telefono) 
                VALUES (:nombre, :usuario, :password, :tipo_usuario, :telefono)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'nombre' => $nombre,
            'usuario' => $usuario,
            'password' => $password_hash,
            'tipo_usuario' => $tipo_usuario,
            'telefono' => $telefono
        ]);

        if ($result) {
            $_SESSION['success'] = "Usuario registrado correctamente";
            // Registrar la acción en el log
            $user_id = $conn->lastInsertId();
            $admin_id = $_SESSION['user']['id'];
            $log_message = "Usuario ID: $user_id creado por Admin ID: $admin_id";
            error_log($log_message);
        } else {
            $_SESSION['error'] = "Error al registrar el usuario";
        }

    } catch (PDOException $e) {
        error_log("Error en insert_usuario.php: " . $e->getMessage());
        $_SESSION['error'] = "Error al registrar el usuario";
    }

    header('Location: ../registrousuario.php');
    exit();
} else {
    // Si no es POST, redirigir
    header('Location: ../registrousuario.php');
    exit();
}
?> 