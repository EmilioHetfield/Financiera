<?php
require_once '../config.php';
require_once 'session_handler.php';
checkSession();

// Validar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Método no permitido.");
}

try {
    // Validación básica
    if (
        empty($_POST['cliente_id']) || 
        empty($_POST['monto']) || 
        empty($_POST['plazo_semanas']) || 
        empty($_POST['firma_cliente'])
    ) {
        throw new Exception("Datos incompletos.");
    }

    // Capturar datos
    $cliente_id = intval($_POST['cliente_id']);
    $monto = floatval($_POST['monto']);
    $plazo_semanas = intval($_POST['plazo_semanas']);
    $tipo_prestamo = $_POST['tipo_prestamo'];
    $firma_cliente = $_POST['firma_cliente'];
    $prestamo_anterior_id = intval($_POST['prestamo_anterior_id'] ?? 0);
    $firma_aval = $_POST['firma_aval'] ?? null;

    // Verificar que el cliente no tenga préstamos activos
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS prestamos_activos 
        FROM prestamos 
        WHERE cliente_id = ? AND estado = 'Pendiente'
    ");
    $stmt->execute([$cliente_id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado['prestamos_activos'] > 0) {
        throw new Exception("El cliente ya tiene un préstamo en curso.");
    }

    // Calcular saldo pendiente del préstamo anterior
    $saldo_pendiente = 0;
    if ($prestamo_anterior_id) {
        $stmt = $conn->prepare("
            SELECT p.saldo_restante
            FROM prestamos as p 
            WHERE p.id = ? AND p.cliente_id = ?
            GROUP BY p.id
        ");
        $stmt->execute([$prestamo_anterior_id, $cliente_id]);
        $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prestamo) {
            throw new Exception("No se encontró el préstamo anterior.");
        }

        $saldo_pendiente = floatval($prestamo['saldo_pendiente']);
    }

    // Sumar saldo pendiente al monto solicitado
    $nuevo_monto_total = $monto + $saldo_pendiente;

    // Almacenar la firma del cliente
    $firma_cliente_ruta = guardarFirma($firma_cliente, "firma_cliente_$cliente_id");

    // Almacenar la firma del aval (si existe)
    $firma_aval_ruta = null;
    if ($tipo_prestamo === 'aval' && !empty($firma_aval)) {
        $firma_aval_ruta = guardarFirma($firma_aval, "firma_aval_$cliente_id");
    }

    // Insertar nuevo préstamo (renovación)
    $stmt = $conn->prepare("
        INSERT INTO prestamos (
            cliente_id, 
            monto, 
            plazo, 
            tasa_interes, 
            estado, 
            ruta_firma_prestamo, 
            fecha_solicitud, 
            frecuencia_pago
        ) VALUES (
            ?, ?, ?, 40.00, 'Pendiente', ?, NOW(), 'semanal'
        )
    ");

    $stmt->execute([
        $cliente_id,
        $nuevo_monto_total,
        $plazo_semanas,
        $firma_cliente_ruta
    ]);

    // Actualizar estado y saldo del préstamo anterior
    if ($prestamo_anterior_id) {
        $stmt = $conn->prepare("
            UPDATE prestamos 
            SET estado = 'Completado', saldo_restante = 0 
            WHERE id = ?
        ");
        $stmt->execute([$prestamo_anterior_id]);
    }

    // Éxito
    $_SESSION['success'] = "Préstamo renovado exitosamente. El monto total incluye el saldo pendiente anterior.";
    header("Location: ../clientes.php");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: ../clientes.php");
    exit;
}

/**
 * Función para guardar la firma como imagen en el servidor
 */
function guardarFirma($base64, $nombre) {
    $ruta_directorio = '../firmas/';
    if (!file_exists($ruta_directorio)) {
        mkdir($ruta_directorio, 0755, true);
    }

    $ruta_firma = $ruta_directorio . $nombre . '.png';
    $imagen_base64 = explode(',', $base64);
    file_put_contents($ruta_firma, base64_decode($imagen_base64[1]));

    return $ruta_firma;
}
