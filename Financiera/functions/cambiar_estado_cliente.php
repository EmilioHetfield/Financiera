<?php
require_once '../config.php';
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    // Verificar permisos
    if (!isset($_SESSION['user']) || $_SESSION['user']['tipo_usuario'] !== 'master') {
        throw new Exception('No tiene permisos para realizar esta acción');
    }

    $cliente_id = intval($_POST['id']);
    $nuevo_estado = $_POST['estado'];

    // Verificar valores válidos
    if (!in_array($nuevo_estado, ['Activo', 'Inactivo'])) {
        throw new Exception('Estado no válido');
    }

    // Debug de valores
    error_log("ID del cliente: " . $cliente_id);
    error_log("Nuevo estado: " . $nuevo_estado);

    // Verificar que el cliente existe y obtener su estado actual
    $stmt = $conn->prepare("SELECT id, estado FROM clientes WHERE id = ?");
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        throw new Exception('Cliente no encontrado');
    }

    error_log("Estado actual del cliente: " . ($cliente['estado'] ?? 'No definido'));

    // Actualizar el estado
    $stmt = $conn->prepare("UPDATE clientes SET estado = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $cliente_id]);

    // Verificar la actualización
    $filas_afectadas = $stmt->rowCount();
    error_log("Filas afectadas: " . $filas_afectadas);

    // Verificar el nuevo estado
    $stmt = $conn->prepare("SELECT estado FROM clientes WHERE id = ?");
    $stmt->execute([$cliente_id]);
    $estado_actualizado = $stmt->fetchColumn();
    error_log("Estado después de la actualización: " . $estado_actualizado);

    if ($filas_afectadas > 0 || $estado_actualizado === $nuevo_estado) {
        echo json_encode([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'cliente_id' => $cliente_id,
            'nuevo_estado' => $nuevo_estado,
            'estado_anterior' => $cliente['estado'] ?? 'No definido'
        ]);
    } else {
        throw new Exception('No se pudo actualizar el estado del cliente. Estado actual: ' . ($estado_actualizado ?? 'No definido'));
    }

} catch (Exception $e) {
    error_log("Error en cambiar_estado_cliente.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'post_data' => $_POST,
            'error' => $e->getMessage()
        ]
    ]);
}
?> 