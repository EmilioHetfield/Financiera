<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../pages-login.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user']['id'];
        $nombre = $_POST['full_name'];
        $usuario = $_POST['email'];
        $telefono = $_POST['phone'];

        // Validar que los campos requeridos no estén vacíos
        if (empty($nombre) || empty($usuario)) {
            $_SESSION['error'] = "Nombre y email son obligatorios";
            header('Location: ../users-profile.php');
            exit();
        }

        // Verificar si el email ya existe para otro usuario
        $check_sql = "SELECT id FROM usuarios WHERE usuario = :usuario AND id != :user_id";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([
            'usuario' => $usuario,
            'user_id' => $user_id
        ]);
        
        if ($check_stmt->rowCount() > 0) {
            $_SESSION['error'] = "El email ya está en uso por otro usuario";
            header('Location: ../users-profile.php');
            exit();
        }

        // Actualizar información del usuario
        $sql = "UPDATE usuarios SET 
                nombre = :nombre,
                usuario = :usuario,
                telefono = :telefono
                WHERE id = :user_id";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'nombre' => $nombre,
            'usuario' => $usuario,
            'telefono' => $telefono,
            'user_id' => $user_id
        ]);

        if ($result) {
            // Actualizar la sesión
            $_SESSION['user']['nombre'] = $nombre;
            $_SESSION['user']['usuario'] = $usuario;
            $_SESSION['user']['telefono'] = $telefono;
            $_SESSION['success'] = "Perfil actualizado correctamente";
        } else {
            $_SESSION['error'] = "No se pudo actualizar el perfil";
        }

    } catch (PDOException $e) {
        error_log("Error en update_profile.php: " . $e->getMessage());
        $_SESSION['error'] = "Error al actualizar el perfil";
    }
}

header('Location: ../users-profile.php');
exit();
?> 