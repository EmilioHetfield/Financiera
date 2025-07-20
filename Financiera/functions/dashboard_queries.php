<?php
function getDashboardData($conn) {
    $user_info = $_SESSION['user'];
    $data = [
        'totals' => [],
        'notifications' => [],
        'worker_loans' => [],
        'client_loans' => []
    ];

    try {
        // Consulta base para préstamos de clientes
        $clientLoansQuery = "
            SELECT 
                p.id,
                c.nombre_completo as first_name,
                c.id as client_number,
                p.monto as requested_amount,
                p.monto_autorizado as authorized_amount,
                c.id_vendedor,
                p.estado_solicitud,
                p.fecha_solicitud
            FROM prestamos p
            JOIN clientes c ON p.cliente_id = c.id
            WHERE 1=1";

        $params = [];

        // Filtrar por vendedor si el usuario es vendedor
        if ($user_info['tipo_usuario'] === 'vendedor') {
            $clientLoansQuery .= " AND c.id_vendedor = :vendedor_id";
            $params[':vendedor_id'] = $user_info['id'];
        }

        // Agregar ORDER BY para ordenar los resultados
        $clientLoansQuery .= " ORDER BY p.fecha_solicitud DESC LIMIT 10";

        $stmt = $conn->prepare($clientLoansQuery);
        $stmt->execute($params);
        $data['client_loans'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Solo obtener datos de trabajadores si es master
        if ($user_info['tipo_usuario'] === 'master') {
            // Obtener totales
            $stmt = $conn->query("
                SELECT 
                    (SELECT COUNT(*) FROM clientes) as total_clients,
                    (SELECT COUNT(*) FROM usuarios WHERE tipo_usuario IN ('vendedor', 'autorizador', 'cobrador')) as total_workers,
                    (SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = 'vendedor') as total_sellers,
                    (SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = 'cobrador') as total_collectors,
                    (SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = 'autorizador') as total_authorizers
                FROM dual
            ");
            $data['totals'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Obtener todos los trabajadores
            $stmt = $conn->query("
                SELECT 
                    id,
                    nombre as full_name,
                    telefono as mobile_number,
                    tipo_usuario as id_type,
                    usuario as nickname,
                    CASE 
                        WHEN tipo_usuario = 'vendedor' THEN 'success'
                        WHEN tipo_usuario = 'autorizador' THEN 'primary'
                        WHEN tipo_usuario = 'cobrador' THEN 'warning'
                        ELSE 'secondary'
                    END as badge_color
                FROM usuarios
                WHERE tipo_usuario IN ('vendedor', 'autorizador', 'cobrador')
                ORDER BY 
                    CASE tipo_usuario
                        WHEN 'vendedor' THEN 1
                        WHEN 'autorizador' THEN 2
                        WHEN 'cobrador' THEN 3
                    END,
                    nombre
            ");
            $data['worker_loans'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;

    } catch (PDOException $e) {
        error_log("Error en getDashboardData: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return $data;
    }
}

function getNotifications($conn, $user_id) {
    try {
        $sql = "SELECT n.*, 
                       l.loan_type,
                       l.loan_status,
                       c.first_name,
                       c.middle_name,
                       c.last_name,
                       c.form_fill_date
                FROM notifications n
                LEFT JOIN loan_information l ON n.loan_id = l.id
                LEFT JOIN clientes c ON l.client_id = c.id
                WHERE n.user_id = :user_id 
                AND n.notification_status = 0
                ORDER BY n.created_at DESC
                LIMIT 5";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Función para obtener las últimas 5 notificaciones de préstamos
function getUltimosPrestamos($conn, $user_info) {
    try {
        if (!$user_info || !isset($user_info['tipo_usuario'])) {
            return [];
        }

        // Verificar si es admin (master o autorizador)
        $es_admin = in_array($user_info['tipo_usuario'], ['master', 'autorizador']);

        $sql = "SELECT 
                p.id,
                c.nombre_completo as nombre_cliente,
                p.monto,
                p.monto_autorizado,
                p.fecha_solicitud,
                CASE 
                    WHEN p.estado_solicitud = 'pendiente' THEN 'Pendiente'
                    WHEN p.estado_solicitud = 'aprobado' THEN 'Aprobado'
                    WHEN p.estado_solicitud = 'rechazado' THEN 'Rechazado'
                    ELSE 'Pendiente'
                END as estado_solicitud,
                p.estado
            FROM prestamos p
            JOIN clientes c ON p.cliente_id = c.id
            WHERE 1=1";

        if (!$es_admin) {
            $sql .= " AND c.id_vendedor = :vendedor_id";
        }

        $sql .= " ORDER BY p.fecha_solicitud DESC LIMIT 5";

        $stmt = $conn->prepare($sql);
        
        if (!$es_admin) {
            $stmt->bindValue(':vendedor_id', $user_info['id'], PDO::PARAM_INT);
        }

        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar los resultados para el formato esperado por las notificaciones
        $notificaciones = array_map(function($row) {
            return [
                'nombre_cliente' => $row['nombre_cliente'],
                'monto' => $row['monto_autorizado'] ?? $row['monto'],
                'estado_solicitud' => $row['estado_solicitud'],
                'fecha' => $row['fecha_solicitud']
            ];
        }, $resultados);
        
        error_log("Notificaciones procesadas: " . print_r($notificaciones, true));
        
        return $notificaciones;
    } catch (PDOException $e) {
        error_log("Error en getUltimosPrestamos: " . $e->getMessage());
        return [];
    }
}