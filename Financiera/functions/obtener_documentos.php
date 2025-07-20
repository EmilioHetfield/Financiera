<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user'])) {
    die(json_encode([]));
}

try {
    $cliente_id = $_GET['cliente_id'];
    
    $stmt = $conn->prepare("
        SELECT * FROM documentos 
        WHERE cliente_id = ? 
        ORDER BY fecha_subida DESC
    ");
    $stmt->execute([$cliente_id]);
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo json_encode([]);
} 