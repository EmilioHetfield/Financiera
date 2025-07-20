<?php
require_once '../config.php';
require_once 'session_handler.php';
checkSession();

try {
    // Verificar si se recibieron los datos necesarios
    if (!isset($_POST['cliente_id'], $_POST['nombre_completo'], $_POST['fecha_nacimiento'], $_POST['email'], $_POST['telefono'], $_POST['genero'], $_POST['direccion'], $_POST['ciudad'], $_POST['estado'], $_POST['codigo_postal'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $cliente_id = intval($_POST['cliente_id']);
    $nombre_completo = trim($_POST['nombre_completo']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $genero = $_POST['genero'];
    $direccion = trim($_POST['direccion']);
    $ciudad = trim($_POST['ciudad']);
    $estado = trim($_POST['estado']);
    $codigo_postal = trim($_POST['codigo_postal']);

    // Iniciar transacción
    $conn->beginTransaction();

    // Actualizar información del cliente
    $stmt = $conn->prepare("
        UPDATE clientes SET 
        nombre_completo = ?, 
        fecha_nacimiento = ?, 
        email = ?, 
        telefono = ?, 
        genero = ? 
        WHERE id = ?
    ");
    $stmt->execute([$nombre_completo, $fecha_nacimiento, $email, $telefono, $genero, $cliente_id]);

    // Verificar si ya existe una dirección para el cliente
    $stmt = $conn->prepare("SELECT id FROM direcciones WHERE cliente_id = ?");
    $stmt->execute([$cliente_id]);
    $direccion_existente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($direccion_existente) {
        // Actualizar dirección existente
        $stmt = $conn->prepare("
            UPDATE direcciones SET 
            direccion = ?, 
            ciudad = ?, 
            estado = ?, 
            codigo_postal = ? 
            WHERE cliente_id = ?
        ");
        $stmt->execute([$direccion, $ciudad, $estado, $codigo_postal, $cliente_id]);
    } else {
        // Insertar nueva dirección
        $stmt = $conn->prepare("
            INSERT INTO direcciones (cliente_id, direccion, ciudad, estado, codigo_postal) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$cliente_id, $direccion, $ciudad, $estado, $codigo_postal]);
    }

    // Confirmar transacción
    $conn->commit();

    // Redirigir a la lista de clientes
    header('Location: ../clientes.php');
    exit;

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    error_log("Error al actualizar cliente: " . $e->getMessage());
    // Redirigir a la página de edición con un mensaje de error
    header('Location: ../editar_cliente.php?id=' . $cliente_id . '&error=' . urlencode($e->getMessage()));
    exit;
}
?> 