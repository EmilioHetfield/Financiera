<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Verificar acceso
    if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['tipo_usuario'], ['master', 'vendedor'])) {
        throw new Exception('No tiene permisos para realizar esta acción');
    }

    // Validar datos requeridos
    $campos_requeridos = [
        'prestamo_id',
        'tipo_pagare',
        'nombre_cliente',
        'monto',
        'fecha',
        'fecha_limite_pago',
        'firma_cliente'
    ];

    $datos = json_decode(file_get_contents('php://input'), true);
    
    foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo]) || empty($datos[$campo])) {
            throw new Exception("El campo {$campo} es requerido");
        }
    }

    // Validar datos adicionales para pagaré con aval
    if ($datos['tipo_pagare'] === 'con_aval') {
        $campos_aval = ['nombre_aval', 'direccion_aval', 'telefono_aval', 'firma_aval'];
        foreach ($campos_aval as $campo) {
            if (empty($datos[$campo])) {
                throw new Exception("Para pagaré con aval, el campo {$campo} es requerido");
            }
        }
    }

    // Iniciar transacción
    $conn->beginTransaction();

    // Guardar firmas
    $directorio_firmas = __DIR__ . '/../uploads/firmas/';
    if (!file_exists($directorio_firmas)) {
        mkdir($directorio_firmas, 0777, true);
    }

    // Guardar firma del cliente
    $firma_cliente_nombre = 'firma_cliente_' . time() . '_' . uniqid() . '.png';
    $firma_cliente_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $datos['firma_cliente']));
    file_put_contents($directorio_firmas . $firma_cliente_nombre, $firma_cliente_data);
    $ruta_firma_cliente = 'uploads/firmas/' . $firma_cliente_nombre;

    // Guardar firma del aval si existe
    $ruta_firma_aval = null;
    if ($datos['tipo_pagare'] === 'con_aval') {
        $firma_aval_nombre = 'firma_aval_' . time() . '_' . uniqid() . '.png';
        $firma_aval_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $datos['firma_aval']));
        file_put_contents($directorio_firmas . $firma_aval_nombre, $firma_aval_data);
        $ruta_firma_aval = 'uploads/firmas/' . $firma_aval_nombre;
    }

    // Verificar que no exista un pagaré previo
    $stmt = $conn->prepare("SELECT id FROM pagares WHERE prestamo_id = ?");
    $stmt->execute([$datos['prestamo_id']]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe un pagaré para este préstamo');
    }

    // Insertar pagaré con los nuevos campos
    $sql = "INSERT INTO pagares (
        prestamo_id,
        tipo_pagare,
        nombre_cliente,
        monto,
        fecha,
        fecha_limite_pago,
        ruta_firma_cliente,
        nombre_aval,
        direccion_aval,
        telefono_aval,
        ruta_firma_aval,
        estado,
        multa
    ) VALUES (
        :prestamo_id,
        :tipo_pagare,
        :nombre_cliente,
        :monto,
        :fecha,
        :fecha_limite_pago,
        :ruta_firma_cliente,
        :nombre_aval,
        :direccion_aval,
        :telefono_aval,
        :ruta_firma_aval,
        'Pendiente',
        0.00
    )";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':prestamo_id' => $datos['prestamo_id'],
        ':tipo_pagare' => $datos['tipo_pagare'],
        ':nombre_cliente' => $datos['nombre_cliente'],
        ':monto' => $datos['monto'],
        ':fecha' => $datos['fecha'],
        ':fecha_limite_pago' => $datos['fecha_limite_pago'],
        ':ruta_firma_cliente' => $ruta_firma_cliente,
        ':nombre_aval' => $datos['tipo_pagare'] === 'con_aval' ? $datos['nombre_aval'] : null,
        ':direccion_aval' => $datos['tipo_pagare'] === 'con_aval' ? $datos['direccion_aval'] : null,
        ':telefono_aval' => $datos['tipo_pagare'] === 'con_aval' ? $datos['telefono_aval'] : null,
        ':ruta_firma_aval' => $ruta_firma_aval
    ]);

    // Actualizar estado del préstamo
    $stmt = $conn->prepare("UPDATE prestamos SET estado = 'Con Pagaré' WHERE id = ?");
    $stmt->execute([$datos['prestamo_id']]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Pagaré guardado correctamente'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }

    // Limpiar archivos de firmas si se crearon
    if (isset($firma_cliente_nombre) && file_exists($directorio_firmas . $firma_cliente_nombre)) {
        unlink($directorio_firmas . $firma_cliente_nombre);
    }
    if (isset($firma_aval_nombre) && file_exists($directorio_firmas . $firma_aval_nombre)) {
        unlink($directorio_firmas . $firma_aval_nombre);
    }

    error_log("Error en procesar_pagare.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 