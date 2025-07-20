<?php
require_once '../config.php';
require_once 'session_handler.php';
checkSession();

header('Content-Type: application/json');

if (!isset($_GET['cliente_id'])) {
    echo json_encode(['error' => 'ID de cliente no proporcionado']);
    exit;
}

$cliente_id = intval($_GET['cliente_id']);

try {
    $stmt = $conn->prepare("
        SELECT 
            id,
            monto,
            fecha_solicitud,
            estado_solicitud,
            monto_autorizado,
            plazo_semanas,
            saldo_restante,
            monto_total,
            (CASE 
                WHEN saldo_restante <= (monto_total * 0.1)  -- Aquí se verifica si el saldo es menor o igual al 10%
                AND estado_solicitud = 'aprobado'           -- Y que el préstamo esté aprobado
                THEN true 
                ELSE false 
            END) as puede_renovar                          -- Este campo booleano determina si se muestra el botón
        FROM prestamos 
        WHERE cliente_id = ?
        ORDER BY fecha_solicitud DESC
    ");
    
    $stmt->execute([$cliente_id]);
    $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($prestamos);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener el historial de préstamos: ' . $e->getMessage()]);
} 