<?php
require_once __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $pagina = isset($data['pagina']) ? (int)$data['pagina'] : 1;
    $registros_por_pagina = isset($data['registros_por_pagina']) ? (int)$data['registros_por_pagina'] : 10;
    $offset = ($pagina - 1) * $registros_por_pagina;
    
    // Construir consulta base
    $sql = "SELECT 
                p.*,
                c.nombre_completo as nombre_cliente,
                u.nombre as nombre_vendedor
            FROM prestamos p
            JOIN clientes c ON p.cliente_id = c.id
            JOIN usuarios u ON c.id_vendedor = u.id
            WHERE p.estado_solicitud = 'pendiente'";
    
    $params = [];
    
    // Aplicar filtros
    if (!empty($data['cliente'])) {
        $sql .= " AND c.nombre_completo LIKE ?";
        $params[] = "%{$data['cliente']}%";
    }
    
    if (!empty($data['vendedor'])) {
        $sql .= " AND c.id_vendedor = ?";
        $params[] = $data['vendedor'];
    }
    
    if (!empty($data['fecha'])) {
        $sql .= " AND DATE(p.fecha_solicitud) = ?";
        $params[] = $data['fecha'];
    }
    
    // Obtener total de registros
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ($sql) as total");
    $stmt->execute($params);
    $total_registros = $stmt->fetchColumn();
    
    // Agregar límite para paginación
    $sql .= " ORDER BY p.fecha_solicitud DESC LIMIT ? OFFSET ?";
    $params[] = $registros_por_pagina;
    $params[] = $offset;
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'prestamos' => $prestamos,
        'total_registros' => $total_registros,
        'total_paginas' => ceil($total_registros / $registros_por_pagina),
        'pagina_actual' => $pagina
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los préstamos: ' . $e->getMessage()
    ]);
} 