<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    try {
        // Preparar la consulta
        $sql = "SELECT id, nombre, usuario, tipo_usuario FROM usuarios WHERE usuario = :usuario AND password = :password";
        $stmt = $conn->prepare($sql);
        
        // Vincular parámetros
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->bindParam(':password', md5($password), PDO::PARAM_STR);
        
        // Ejecutar la consulta
        $stmt->execute();

        // Verificar si se encontró el usuario
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'usuario' => $user['usuario'],
                'tipo_usuario' => $user['tipo_usuario']
            ];

            $_SESSION['user_id'] = $user['id'];

            error_log("Usuario {$user['usuario']} ({$user['tipo_usuario']}) ha iniciado sesión exitosamente");
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos";
            error_log("Intento de inicio de sesión fallido para el usuario: $usuario");
            header("Location: pages-login.html?error=" . urlencode($error));
            exit();
        }

    } catch (PDOException $e) {
        error_log("Error en login.php: " . $e->getMessage());
        $error = "Error al intentar iniciar sesión";
        header("Location: pages-login.html?error=" . urlencode($error));
        exit();
    }
}

// Si se llega aquí sin POST, redirigir al formulario de login
if (!isset($_SESSION['user'])) {
    header("Location: pages-login.html");
    exit();
}

if ($user) {
    session_start();
    $_SESSION['user'] = $user;
    $_SESSION['last_activity'] = time(); // Agregar tiempo inicial
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
}
?>