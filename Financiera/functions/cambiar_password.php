<?php
require_once __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');

try {
    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['user'])) {
        throw new Exception('Acceso no autorizado');
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['nueva_password'])) {
        throw new Exception('Datos incompletos');
    }

    if ($data['nueva_password'] !== $data['confirmar_password']) {
        throw new Exception('Las nuevas contraseñas no coinciden');
    }

    // Determinar el usuario cuyo password se va a cambiar
    $usuario_id = $_SESSION['user']['id'];

    // Si el usuario es "master", puede cambiar la contraseña de cualquier usuario
    if ($_SESSION['user']['tipo_usuario'] === 'master' && !empty($data['usuario_id'])) {
        $usuario_id = $data['usuario_id'];
    } else {
        // Verificar la contraseña actual solo si el usuario no es "master"
        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || md5($data['current_password']) !== $usuario['password']) {
            throw new Exception('Contraseña actual incorrecta');
        }
    }

    // Actualizar a la nueva contraseña
    $new_password_md5 = md5($data['nueva_password']);
    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $stmt->execute([$new_password_md5, $usuario_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Contraseña actualizada exitosamente'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 