<?php

class PrestamoManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function obtenerPrestamo($id) {
        try {
            $sql = "SELECT 
                        p.*,
                        c.nombre_completo as nombre_cliente,
                        c.telefono as telefono_cliente
                    FROM prestamos p
                    JOIN clientes c ON p.cliente_id = c.id
                    WHERE p.id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            
            $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$prestamo) {
                return null;
            }
            
            // Verificar acceso según el rol
            if ($_SESSION['user']['tipo_usuario'] !== 'master') {
                $sql = "SELECT id_vendedor FROM clientes WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$prestamo['cliente_id']]);
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($cliente['id_vendedor'] != $_SESSION['user']['id']) {
                    return null; // No tiene acceso a este préstamo
                }
            }
            
            return $prestamo;
            
        } catch (Exception $e) {
            error_log("Error en obtenerPrestamo: " . $e->getMessage());
            return null;
        }
    }
} 