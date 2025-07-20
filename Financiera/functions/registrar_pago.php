<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'));
    
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Usuario no autorizado');
    }

    // Iniciar transacción
    $conn->beginTransaction();

    // Insertar el pago
    $sql = "INSERT INTO pagos (
        pagare_id, 
        tipo_pago, 
        monto, 
        fecha_pago, 
        observaciones, 
        registrado_por
    ) VALUES (
        :pagare_id,
        :tipo_pago,
        :monto,
        :fecha_pago,
        :observaciones,
        :registrado_por
    )";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':pagare_id' => $data->prestamo_id,
        ':tipo_pago' => $data->tipo_pago,
        ':monto' => $data->monto,
        ':fecha_pago' => $data->fecha_pago,
        ':observaciones' => $data->observaciones,
        ':registrado_por' => $_SESSION['user_id']
    ]);

    // Actualizar saldo restante en préstamo
    $sql = "UPDATE prestamos 
            SET saldo_restante = saldo_restante - :monto 
            WHERE id = :prestamo_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':monto' => $data->monto,
        ':prestamo_id' => $data->prestamo_id
    ]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Pago registrado exitosamente'
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 