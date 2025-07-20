<?php
require_once __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Debug de datos recibidos
    error_log("Datos recibidos: " . print_r($data, true));
    
    $pagina = isset($data['pagina']) ? (int)$data['pagina'] : 1;
    $registros_por_pagina = isset($data['registros_por_pagina']) ? (int)$data['registros_por_pagina'] : 10;
    $offset = ($pagina - 1) * $registros_por_pagina;
    
    // Construir consulta base
    $sql = "SELECT 
                p.id,
                p.monto,
                p.plazo,
                p.tasa_interes,
                p.estado,
                p.fecha_solicitud,
                p.estado_solicitud,
                c.nombre_completo as nombre_cliente,
                c.id_vendedor
            FROM prestamos p
            JOIN clientes c ON p.cliente_id = c.id
            WHERE 1=1";
    
    $params = [];
    
    // Filtrar por vendedor si no es master
    if (isset($_SESSION['user']['tipo_usuario']) && $_SESSION['user']['tipo_usuario'] !== 'master') {
        $sql .= " AND c.id_vendedor = ?";
        $params[] = $_SESSION['user']['id'];
    }
    
    // Aplicar filtro de estado_solicitud
    if (!empty($data['estado_solicitud'])) {
        $sql .= " AND p.estado_solicitud = ?";
        $params[] = $data['estado_solicitud'];
        error_log("Aplicando filtro estado_solicitud: " . $data['estado_solicitud']);
    }
    
    // Aplicar filtro de fecha
    if (!empty($data['fecha'])) {
        $sql .= " AND DATE(p.fecha_solicitud) = ?";
        $params[] = $data['fecha'];
        error_log("Aplicando filtro fecha: " . $data['fecha']);
    }
    
    // Obtener total de registros para paginación
    $sqlCount = "SELECT COUNT(*) FROM ($sql) as total";
    $stmtCount = $conn->prepare($sqlCount);
    $stmtCount->execute($params);
    $total_registros = $stmtCount->fetchColumn();
    
    // Agregar ordenamiento y límites
    $sql .= " ORDER BY p.fecha_solicitud DESC LIMIT ? OFFSET ?";
    $params[] = $registros_por_pagina;
    $params[] = $offset;
    
    // Debug de la consulta final
    error_log("SQL Query: " . $sql);
    error_log("Parámetros: " . print_r($params, true));
    
    // Ejecutar consulta principal
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug de resultados
    error_log("Total registros encontrados: " . $total_registros);
    error_log("Registros en página actual: " . count($prestamos));
    
    echo json_encode([
        'success' => true,
        'prestamos' => $prestamos,
        'total_registros' => $total_registros,
        'total_paginas' => ceil($total_registros / $registros_por_pagina),
        'pagina_actual' => $pagina,
        'filtros_aplicados' => [
            'estado_solicitud' => $data['estado_solicitud'] ?? null,
            'fecha' => $data['fecha'] ?? null
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error en obtener_prestamos.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los préstamos: ' . $e->getMessage()
    ]);
} 