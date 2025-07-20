<?php
require_once __DIR__ . '/../config.php';

class PagareManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Verifica si ya existe un pagaré para el préstamo
     */
    private function existePagare($prestamo_id) {
        $sql = "SELECT COUNT(*) FROM pagares WHERE prestamo_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$prestamo_id]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Registra un nuevo pagaré
     */
    public function registrarPagare($datos) {
        try {
            // Verificar si ya existe un pagaré para este préstamo
            if ($this->existePagare($datos['prestamo_id'])) {
                throw new Exception("Ya existe un pagaré registrado para este préstamo");
            }

            $this->conn->beginTransaction();
            
            // 1. Validar datos
            $this->validarDatos($datos);
            
            // 2. Procesar y guardar firmas
            $ruta_firma_cliente = $this->procesarFirma($datos['firma_cliente'], 'cliente');
            $ruta_firma_aval = null;
            if (!empty($datos['firma_aval'])) {
                $ruta_firma_aval = $this->procesarFirma($datos['firma_aval'], 'aval');
            }
            
            // 3. Insertar pagaré
            $pagare_id = $this->insertarPagare($datos, $ruta_firma_cliente, $ruta_firma_aval);
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'pagare_id' => $pagare_id,
                'message' => 'Pagaré registrado exitosamente'
            ];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error en registrarPagare: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Valida los datos del pagaré
     */
    private function validarDatos($datos) {
        $errores = [];
        
        if (empty($datos['prestamo_id'])) {
            $errores[] = "El ID del préstamo es requerido";
        }
        if (empty($datos['nombre_cliente'])) {
            $errores[] = "El nombre del cliente es requerido";
        }
        if (empty($datos['monto']) || $datos['monto'] <= 0) {
            $errores[] = "El monto debe ser mayor a 0";
        }
        if (empty($datos['fecha'])) {
            $errores[] = "La fecha es requerida";
        }
        if (empty($datos['fecha_limite_pago'])) {
            $errores[] = "La fecha límite de pago es requerida";
        }
        if (empty($datos['firma_cliente'])) {
            $errores[] = "La firma del cliente es requerida";
        }
        
        // Validar que la fecha límite sea posterior a la fecha de emisión
        if (!empty($datos['fecha']) && !empty($datos['fecha_limite_pago'])) {
            $fecha_emision = new DateTime($datos['fecha']);
            $fecha_limite = new DateTime($datos['fecha_limite_pago']);
            if ($fecha_limite <= $fecha_emision) {
                $errores[] = "La fecha límite debe ser posterior a la fecha de emisión";
            }
        }
        
        // Validaciones específicas para pagaré con aval
        if ($datos['tipo_pagare'] === 'con_aval') {
            if (empty($datos['nombre_aval'])) {
                $errores[] = "El nombre del aval es requerido";
            }
            if (empty($datos['firma_aval'])) {
                $errores[] = "La firma del aval es requerida";
            }
        }
        
        if (!empty($errores)) {
            throw new Exception(implode(", ", $errores));
        }
    }
    
    /**
     * Procesa y guarda una firma
     */
    private function procesarFirma($firma_data, $tipo) {
        if (strpos($firma_data, 'data:image/png;base64,') === false) {
            throw new Exception("Formato de firma inválido");
        }
        
        $firma_data = str_replace('data:image/png;base64,', '', $firma_data);
        $firma_data = str_replace(' ', '+', $firma_data);
        $firma_decodificada = base64_decode($firma_data);
        
        if ($firma_decodificada === false) {
            throw new Exception("Error al decodificar la firma");
        }
        
        // Crear directorio si no existe
        $upload_dir = __DIR__ . '/../uploads/firmas_pagares/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Error al crear el directorio para firmas");
            }
        }
        
        // Generar nombre único para el archivo
        $nombre_archivo = uniqid("firma_{$tipo}_") . '.png';
        $ruta_completa = $upload_dir . $nombre_archivo;
        
        if (!file_put_contents($ruta_completa, $firma_decodificada)) {
            throw new Exception("Error al guardar la firma");
        }
        
        return $nombre_archivo;
    }
    
    /**
     * Inserta el pagaré en la base de datos
     */
    private function insertarPagare($datos, $ruta_firma_cliente, $ruta_firma_aval) {
        $sql = "INSERT INTO pagares (
            prestamo_id,
            tipo_pagare,
            nombre_cliente,
            monto,
            fecha,
            fecha_limite_pago,
            ruta_firma_cliente,
            nombre_aval,
            ruta_firma_aval,
            estado
        ) VALUES (
            :prestamo_id,
            :tipo_pagare,
            :nombre_cliente,
            :monto,
            :fecha,
            :fecha_limite_pago,
            :ruta_firma_cliente,
            :nombre_aval,
            :ruta_firma_aval,
            'Pendiente'
        )";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':prestamo_id' => $datos['prestamo_id'],
            ':tipo_pagare' => $datos['tipo_pagare'],
            ':nombre_cliente' => $datos['nombre_cliente'],
            ':monto' => $datos['monto'],
            ':fecha' => $datos['fecha'],
            ':fecha_limite_pago' => $datos['fecha_limite_pago'],
            ':ruta_firma_cliente' => $ruta_firma_cliente,
            ':nombre_aval' => $datos['nombre_aval'] ?? null,
            ':ruta_firma_aval' => $ruta_firma_aval
        ]);
        
        return $this->conn->lastInsertId();
    }
}

// Archivo para procesar la solicitud del formulario 