<?php
require_once '../config.php';
session_start();
header('Content-Type: application/json');

try {
    // Obtener y decodificar los datos JSON
    $jsonData = file_get_contents('php://input');
    if (!$jsonData) {
        throw new Exception('No se recibieron datos');
    }

    $data = json_decode($jsonData);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
    }

    // Verificar sesión
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Usuario no autorizado');
    }

    $user_id = $_SESSION['user_id'];

    // Validaciones básicas
    if (!isset($data->prestamo_id, $data->monto_autorizado, $data->plazo_semanas, 
               $data->tasa_interes, $data->fecha_primer_pago)) {
        throw new Exception('Faltan datos requeridos');
    }

    // Calcular monto total
    $monto_total = $data->monto_autorizado + ($data->monto_autorizado * ($data->tasa_interes / 100));

    // Iniciar transacción
    $conn->beginTransaction();

    // Actualizar préstamo
    $sql = "UPDATE prestamos SET 
        estado_solicitud = 'aprobado',
        monto_autorizado = :monto_autorizado,
        plazo_semanas = :plazo_semanas,
        tasa_interes = :tasa_interes,
        fecha_autorizacion = NOW(),
        fecha_primer_pago = :fecha_primer_pago,
        fecha_ultimo_pago = :fecha_ultimo_pago,
        frecuencia_pago = :frecuencia_pago,
        autorizado_por = :autorizado_por,
        saldo_restante = :monto_total
    WHERE id = :prestamo_id";

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        ':monto_autorizado' => $data->monto_autorizado,
        ':plazo_semanas' => $data->plazo_semanas,
        ':tasa_interes' => $data->tasa_interes,
        ':fecha_primer_pago' => $data->fecha_primer_pago,
        ':fecha_ultimo_pago' => $data->fecha_ultimo_pago,
        ':frecuencia_pago' => $data->frecuencia_pago,
        ':autorizado_por' => $user_id,
        ':monto_total' => $monto_total,
        ':prestamo_id' => $data->prestamo_id
    ]);

    if (!$result) {
        throw new Exception('Error al actualizar el préstamo');
    }

    // Confirmar transacción
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Préstamo autorizado exitosamente'
    ]);

} catch (Exception $e) {
    // Rollback en caso de error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    // Respuesta de error en formato JSON
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 