<?php
require_once __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['prestamo_id'])) {
        throw new Exception('ID de préstamo no proporcionado');
    }

    $sql = "SELECT 
                p.*,
                c.nombre_completo as nombre_cliente,
                c.id_vendedor,
                u.nombre as nombre_vendedor
            FROM prestamos p
            JOIN clientes c ON p.cliente_id = c.id
            JOIN usuarios u ON c.id_vendedor = u.id
            WHERE p.id = :prestamo_id";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([':prestamo_id' => $data['prestamo_id']]);
    $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prestamo) {
        throw new Exception('Préstamo no encontrado');
    }

    // Si el usuario es vendedor, verificar que el préstamo sea de sus clientes
    if ($_SESSION['user']['tipo_usuario'] === 'vendedor' && 
        $prestamo['id_vendedor'] !== $_SESSION['user']['id']) {
        throw new Exception('No tiene permiso para ver este préstamo');
    }
    
    // Debug
    error_log('Préstamo encontrado: ' . print_r($prestamo, true));
    
    echo json_encode([
        'success' => true,
        'prestamo' => $prestamo
    ]);
    
} catch (Exception $e) {
    error_log('Error en obtener_detalle_prestamo: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 