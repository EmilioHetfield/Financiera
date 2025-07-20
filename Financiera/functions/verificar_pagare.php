<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception('Sesión no válida');
    }

    $datos = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($datos['prestamo_id'])) {
        throw new Exception('ID de préstamo no proporcionado');
    }

    $stmt = $conn->prepare("SELECT id FROM pagares WHERE prestamo_id = ?");
    $stmt->execute([$datos['prestamo_id']]);
    
    echo json_encode([
        'success' => true,
        'existe' => $stmt->fetch() ? true : false
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 