<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

try {
    // Verificar si se recibió el ID del documento
    if (!isset($_POST['documento_id'])) {
        throw new Exception('ID de documento no proporcionado');
    }

    $documento_id = intval($_POST['documento_id']);

    // Primero obtener la información del documento
    $stmt = $conn->prepare("SELECT ruta, cliente_id FROM documentos WHERE id = ?");
    $stmt->execute([$documento_id]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$documento) {
        throw new Exception('Documento no encontrado');
    }

    // Ruta completa del archivo
    $ruta_archivo = "../" . $documento['ruta'];

    // Eliminar el archivo físico
    if (file_exists($ruta_archivo)) {
        if (!unlink($ruta_archivo)) {
            throw new Exception('No se pudo eliminar el archivo físico');
        }
    }

    // Eliminar el registro de la base de datos
    $stmt = $conn->prepare("DELETE FROM documentos WHERE id = ?");
    $resultado = $stmt->execute([$documento_id]);

    if (!$resultado) {
        throw new Exception('Error al eliminar el registro de la base de datos');
    }

    // Verificar si la carpeta está vacía y eliminarla si es necesario
    $carpeta_cliente = "../uploads/documentos/{$documento['cliente_id']}/";
    if (is_dir($carpeta_cliente)) {
        $archivos = array_diff(scandir($carpeta_cliente), array('.', '..'));
        if (empty($archivos)) {
            rmdir($carpeta_cliente);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Documento eliminado correctamente'
    ]);

} catch (Exception $e) {
    error_log("Error al eliminar documento: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 