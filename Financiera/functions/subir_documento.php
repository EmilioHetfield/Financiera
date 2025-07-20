<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

try {
    // Verificar si se recibieron los datos necesarios
    if (!isset($_POST['cliente_id']) || !isset($_FILES['documento']) || !isset($_POST['tipo_documento'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $cliente_id = intval($_POST['cliente_id']);
    $archivo = $_FILES['documento'];
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
    $tipo_documento = isset($_POST['tipo_documento']) ? trim($_POST['tipo_documento']) : null;

    // Validar que se haya seleccionado un tipo de documento
    if (empty($tipo_documento)) {
        throw new Exception('Debe seleccionar un tipo de documento');
    }

    // Validar el tipo de archivo
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($archivo['type'], $allowed_types)) {
        throw new Exception('Tipo de archivo no permitido. Solo se permiten PDF, JPEG y PNG.');
    }

    // Crear directorio si no existe
    $upload_dir = "../uploads/documentos/{$cliente_id}/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generar nombre único para el archivo
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid() . '_' . date('Ymd') . '.' . $extension;
    $ruta_completa = $upload_dir . $nombre_archivo;

    // Mover el archivo
    if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        throw new Exception('Error al subir el archivo');
    }

    // Guardar información en la base de datos
    $stmt = $conn->prepare("
        INSERT INTO documentos 
        (cliente_id, nombre_archivo, ruta, descripcion, tipo_documento) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $ruta_relativa = "uploads/documentos/{$cliente_id}/" . $nombre_archivo;
    $stmt->execute([
        $cliente_id, 
        $archivo['name'], 
        $ruta_relativa, 
        $descripcion,
        $tipo_documento
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Documento subido correctamente',
        'documento' => [
            'id' => $conn->lastInsertId(),
            'nombre' => $archivo['name'],
            'ruta' => $ruta_relativa,
            'descripcion' => $descripcion ?? 'Sin descripción',
            'tipo_documento' => $tipo_documento
        ]
    ]);

} catch (Exception $e) {
    error_log("Error al subir documento: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 