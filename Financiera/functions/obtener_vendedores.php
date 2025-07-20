<?php
require_once __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');

try {
    $sql = "SELECT 
                id,
                nombre,
                usuario
            FROM usuarios 
            WHERE tipo_usuario = 'vendedor'
            ORDER BY nombre ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $vendedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'vendedores' => $vendedores
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 