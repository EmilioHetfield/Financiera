<?php
require_once __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');

try {
    if ($_SESSION['user']['tipo_usuario'] !== 'master') {
        throw new Exception('Acceso no autorizado');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    $sql = "SELECT 
                id,
                nombre,
                usuario,
                tipo_usuario,
                DATE_FORMAT(ultima_actualizacion, '%d/%m/%Y %H:%i') as ultima_actualizacion
            FROM usuarios
            WHERE tipo_usuario IN ('vendedor', 'autorizador')";
    
    $params = [];
    
    if (!empty($data['tipo'])) {
        $sql .= " AND tipo_usuario = ?";
        $params[] = $data['tipo'];
    }
    
    if (!empty($data['nombre'])) {
        $sql .= " AND nombre LIKE ?";
        $params[] = "%{$data['nombre']}%";
    }
    
    $sql .= " ORDER BY nombre ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'usuarios' => $usuarios
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 