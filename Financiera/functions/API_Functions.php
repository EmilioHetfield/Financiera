<?php
require_once __DIR__ . '/../config.php';

// Asegurarse de que no haya salida antes de los headers
ob_start();

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Verificar que la conexión existe
if (!isset($GLOBALS['conn'])) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]);
    exit();
}

error_log("Iniciando proceso...");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log("Iniciando proceso de registro...");
        
        // Verificar que la conexión existe
        if (!isset($GLOBALS['conn'])) {
            throw new Exception("Error de conexión a la base de datos");
        }
        
        $conn = $GLOBALS['conn'];
        
        // Iniciar sesión y obtener ID del vendedor
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
            throw new Exception("Sesión no válida");
        }
        
        // Validar datos antes de iniciar la transacción
        error_log("Validando datos...");
        validarDatosRequeridos($_POST);
        validarMontos($_POST);
        
        // Verificar si ya hay una transacción activa
        if ($conn->inTransaction()) {
            error_log("Transacción activa encontrada, haciendo rollback");
            $conn->rollBack();
        }
        
        error_log("Iniciando nueva transacción");
        $conn->beginTransaction();
        
        $id_vendedor = $_SESSION['user']['id'];
        error_log("ID del vendedor: " . $id_vendedor);
        
        // Debug de datos recibidos
        error_log("Datos POST recibidos: " . print_r($_POST, true));
        
        // Inicializar variables
        $cliente_id = null;
        $prestamo_id = null;
        
        // Procesar firma antes de insertar cliente
        $nombre_firma = null;
        if (isset($_POST['firma']) && !empty($_POST['firma'])) {
            try {
                // Generar un ID temporal para la firma
                $temp_id = time() . '_' . uniqid();
                $nombre_firma = procesarFirma($_POST['firma'], $temp_id);
            } catch (Exception $e) {
                error_log("Error al guardar la firma: " . $e->getMessage());
                throw $e;
            }
        }

        // Insertar cliente
        $stmt = $conn->prepare("INSERT INTO clientes (
            nombre_completo, 
            fecha_nacimiento, 
            email, 
            telefono, 
            genero,
            id_vendedor,
            ruta_firma
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $_POST['nombre_completo'],
            $_POST['fecha_nacimiento'],
            $_POST['email'],
            $_POST['telefono'],
            $_POST['genero'],
            $id_vendedor,
            $nombre_firma
        ]);
        
        $cliente_id = $conn->lastInsertId();
        error_log("Cliente insertado con ID: " . $cliente_id);

        // Si se guardó una firma temporal, actualizarla con el ID real del cliente
        if ($nombre_firma) {
            $nuevo_nombre = str_replace($temp_id, $cliente_id, $nombre_firma);
            rename(
                __DIR__ . '/../uploads/firmas/' . $nombre_firma,
                __DIR__ . '/../uploads/firmas/' . $nuevo_nombre
            );
            
            // Actualizar el nombre en la base de datos
            $stmt = $conn->prepare("UPDATE clientes SET ruta_firma = ? WHERE id = ?");
            $stmt->execute([$nuevo_nombre, $cliente_id]);
        }
        
        // Insertar datos personales
        $stmt = $conn->prepare("INSERT INTO datos_personales (
            id_cliente,
            rfc,
            curp,
            estado_civil,
            dependientes_economicos,
            tipo_identificacion,
            no_identificacion,
            lugar_nacimiento,
            pais,
            tipo_vivienda,
            tiempo_vivienda,
            nombre_conyuge,
            fecha_nac_conyuge,
            telefono_conyuge,
            ocupacion_conyuge
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Validar y asignar valores con valores por defecto si no existen
        $dependientes = isset($_POST['dependientes']) ? intval($_POST['dependientes']) : 0;
        $lugar_nacimiento = isset($_POST['lugar_nacimiento']) ? strval($_POST['lugar_nacimiento']) : '';
        $pais = isset($_POST['pais']) ? strval($_POST['pais']) : 'México'; // Valor por defecto

        $stmt->execute([
            $cliente_id,
            $_POST['rfc'],
            $_POST['curp'],
            $_POST['estado_civil'],
            $dependientes,
            $_POST['tipo_identificacion'],
            $_POST['no_identificacion'],
            $lugar_nacimiento,
            $pais,
            $_POST['tipo_vivienda'],
            $_POST['tiempo_vivienda'],
            $_POST['nombre_conyuge'] ?? null,
            $_POST['fecha_nac_conyuge'] ?? null,
            $_POST['telefono_conyuge'] ?? null,
            $_POST['ocupacion_conyuge'] ?? null
        ]);
        
        // Debug para ver qué datos se están procesando
        error_log("Datos personales a insertar: " . print_r([
            'cliente_id' => $cliente_id,
            'rfc' => $_POST['rfc'],
            'curp' => $_POST['curp'],
            'estado_civil' => $_POST['estado_civil'],
            'dependientes' => $dependientes,
            'tipo_identificacion' => $_POST['tipo_identificacion'],
            'no_identificacion' => $_POST['no_identificacion'],
            'lugar_nacimiento' => $lugar_nacimiento,
            'pais' => $pais,
            'tipo_vivienda' => $_POST['tipo_vivienda'],
            'tiempo_vivienda' => $_POST['tiempo_vivienda']
        ], true));
        
        $datos_personales_id = $conn->lastInsertId();
        
        // Insertar dependientes
        if (isset($_POST['dependientes']) && is_array($_POST['dependientes'])) {
            $stmt = $conn->prepare("INSERT INTO dependientes (
                id_datos_personales,
                nombre,
                parentesco,
                ocupacion
            ) VALUES (?, ?, ?, ?)");
            
            foreach ($_POST['dependientes'] as $dependiente) {
                $stmt->execute([
                    $datos_personales_id,
                    $dependiente['nombre'],
                    $dependiente['parentesco'],
                    $dependiente['ocupacion'] ?? null
                ]);
            }
        }
        
        // Insertar referencias
        $stmt = $conn->prepare("INSERT INTO referencias (
            id_datos_personales,
            nombre,
            direccion,
            telefono,
            parentesco
        ) VALUES (?, ?, ?, ?, ?)");
        
        // Validar y procesar referencias
        $referencias = [
            [
                'nombre' => $_POST['nombre_ref1'] ?? '',
                'direccion' => $_POST['direccion_ref1'] ?? '',
                'telefono' => $_POST['telefono_ref1'] ?? '',
                'parentesco' => $_POST['parentesco_ref1'] ?? ''
            ],
            [
                'nombre' => $_POST['nombre_ref2'] ?? '',
                'direccion' => $_POST['direccion_ref2'] ?? '',
                'telefono' => $_POST['telefono_ref2'] ?? '',
                'parentesco' => $_POST['parentesco_ref2'] ?? ''
            ]
        ];

        foreach ($referencias as $referencia) {
            // Verificar que todos los campos de la referencia estén presentes
            if (!empty($referencia['nombre']) && !empty($referencia['direccion']) && 
                !empty($referencia['telefono']) && !empty($referencia['parentesco'])) {
                
                $stmt->execute([
                    $datos_personales_id,
                    $referencia['nombre'],
                    $referencia['direccion'],
                    $referencia['telefono'],
                    $referencia['parentesco']
                ]);
            } else {
                throw new Exception("Todos los campos de las referencias son obligatorios");
            }
        }
        
        // Procesar dirección
        $stmt = $conn->prepare("INSERT INTO direcciones (
            cliente_id, 
            direccion, 
            ciudad, 
            estado, 
            codigo_postal
        ) VALUES (?, ?, ?, ?, ?)");
        
        // Validar que existan todos los campos de dirección
        if (empty($_POST['direccion']) || empty($_POST['ciudad']) || 
            empty($_POST['estado']) || empty($_POST['codigo_postal'])) {
            throw new Exception("Todos los campos de dirección son obligatorios");
        }

        $stmt->execute([
            $cliente_id,
            $_POST['direccion'],
            $_POST['ciudad'],
            $_POST['estado'],
            $_POST['codigo_postal']
        ]);
        
        // Insertar datos laborales
        $stmt = $conn->prepare("INSERT INTO datos_laborales (
            id_cliente,
            tipo_empleo,
            ocupacion,
            nombre_empresa,
            periodicidad_ingresos,
            antiguedad_anos,
            antiguedad_meses,
            direccion,
            telefono,
            extension,
            codigo_postal
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $cliente_id,
            $_POST['tipo_empleo'],
            $_POST['ocupacion'],
            $_POST['nombre_empresa'],
            $_POST['periodicidad_ingresos'],
            $_POST['antiguedad_anos'],
            $_POST['antiguedad_meses'],
            $_POST['direccion_empresa'],
            $_POST['telefono_empresa'],
            $_POST['extension'],
            $_POST['codigo_postal_empresa']
        ]);
        
        // Insertar datos financieros
        $stmt = $conn->prepare("INSERT INTO datos_financieros (
            id_cliente,
            ingresos_mensuales,
            gastos_mensuales,
            otros_ingresos,
            fuente_otros_ingresos,
            renta_mensual,
            pago_auto,
            gastos_alimentacion,
            gastos_servicios,
            gastos_transporte,
            gastos_educacion,
            deudas_creditos,
            otros_gastos,
            descripcion_otros_gastos
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Calcular gastos mensuales totales con valores limpios
        $gastos_mensuales = 
            floatval($_POST['gastos_alimentacion']) +
            floatval($_POST['gastos_servicios']) +
            floatval($_POST['gastos_transporte']) +
            floatval($_POST['gastos_educacion']) +
            floatval($_POST['renta_mensual']) +
            floatval($_POST['pago_auto']) +
            floatval($_POST['deudas_creditos']) +
            floatval($_POST['otros_gastos']);

        $stmt->execute([
            $cliente_id,
            floatval($_POST['ingresos_mensuales']),
            $gastos_mensuales,
            floatval($_POST['otros_ingresos']),
            $_POST['fuente_otros_ingresos'] ?? null,
            floatval($_POST['renta_mensual']),
            floatval($_POST['pago_auto']),
            floatval($_POST['gastos_alimentacion']),
            floatval($_POST['gastos_servicios']),
            floatval($_POST['gastos_transporte']),
            floatval($_POST['gastos_educacion']),
            floatval($_POST['deudas_creditos']),
            floatval($_POST['otros_gastos']),
            $_POST['descripcion_otros_gastos'] ?? null
        ]);

        // Insertar condiciones de vivienda
        $stmt = $conn->prepare("INSERT INTO condiciones_vivienda (
            id_cliente,
            internet,
            telefono_fijo,
            telefono_movil,
            refrigerador,
            luz_electrica,
            agua_potable,
            auto_propio,
            tv_cable,
            alumbrado_publico,
            estufa,
            gas,
            observaciones
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $cliente_id,
            $_POST['internet'],
            $_POST['telefono_fijo'],
            $_POST['telefono_movil'],
            $_POST['refrigerador'],
            $_POST['luz_electrica'],
            $_POST['agua_potable'],
            $_POST['auto_propio'],
            $_POST['tv_cable'],
            $_POST['alumbrado_publico'],
            $_POST['estufa'],
            $_POST['gas'],
            $_POST['observaciones'] ?? null
        ]);
        
        // Insertar datos del préstamo
        if (isset($_POST['monto']) && isset($_POST['plazo']) && isset($_POST['tasa_interes']) && isset($_POST['frecuencia_pago'])) {
            error_log("Iniciando inserción de préstamo...");
            
            $stmt = $conn->prepare("INSERT INTO prestamos (
                cliente_id,
                monto,
                plazo,
                tasa_interes,
                frecuencia_pago,
                estado,
                estado_solicitud,
                ruta_firma_prestamo,
                plazo_semanas,
                saldo_restante
            ) VALUES (
                ?, -- cliente_id
                ?, -- monto
                ?, -- plazo
                ?, -- tasa_interes
                ?, -- frecuencia_pago
                'Pendiente', -- estado
                'pendiente', -- estado_solicitud
                ?, -- ruta_firma_prestamo
                ?, -- plazo_semanas
                ? -- saldo_restante
            )");

            // Calcular saldo restante inicial (será igual al monto total)
            $monto = floatval($_POST['monto']);
            $tasa = floatval($_POST['tasa_interes']);
            $monto_total = $monto + ($monto * $tasa / 100);
            $plazo_semanas = intval($_POST['plazo']);

            // Debug de los valores a insertar
            error_log("Valores del préstamo a insertar: " . print_r([
                'cliente_id' => $cliente_id,
                'monto' => $monto,
                'plazo' => $plazo_semanas,
                'tasa_interes' => $tasa,
                'frecuencia_pago' => $_POST['frecuencia_pago'],
                'ruta_firma_prestamo' => $nombre_firma ?? '',
                'plazo_semanas' => $plazo_semanas,
                'saldo_restante' => $monto_total
            ], true));

            $stmt->execute([
                $cliente_id,
                $monto,
                $plazo_semanas,
                $tasa,
                $_POST['frecuencia_pago'],
                $nombre_firma ?? '',
                $plazo_semanas,
                $monto_total
            ]);

            $prestamo_id = $conn->lastInsertId();
            error_log("Préstamo insertado con ID: " . $prestamo_id);

        } else {
            error_log("No se proporcionaron datos del préstamo");
            $prestamo_id = null;
        }

        error_log("Confirmando transacción");
        $conn->commit();
        
        error_log("Proceso completado exitosamente");
        
        // Establecer headers para JSON
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => true,
            'message' => 'Registro completado exitosamente',
            'cliente_id' => $cliente_id,
            'prestamo_id' => $prestamo_id,
            'redirect_url' => 'lista_clientes.php'
        ]);
        
    } catch (Exception $e) {
        error_log("Error en el proceso: " . $e->getMessage());
        
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
        }
        
        // Establecer headers para JSON y error
        header('Content-Type: application/json');
        http_response_code(500);
        
        echo json_encode([
            'success' => false,
            'message' => 'Error al registrar: ' . $e->getMessage()
        ]);
    }

    // Asegurarse de que no haya más salida
    exit();
} else {
    error_log("Método no permitido: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}

// Asegurarse de que no haya salida después del JSON
exit();

// Función para procesar la firma actualizada
function procesarFirma($firma_data, $id) {
    $upload_dir = realpath(__DIR__ . '/../uploads/firmas/');
    if (!$upload_dir) {
        $upload_dir = __DIR__ . '/../uploads/firmas/';
        if (!file_exists($upload_dir) && !mkdir($upload_dir, 0777, true)) {
            throw new Exception("No se pudo crear el directorio para las firmas");
        }
    }
    
    $upload_dir = rtrim($upload_dir, '/\\') . DIRECTORY_SEPARATOR;
    
    if (strpos($firma_data, 'data:image/png;base64,') !== false) {
        $firma_data = str_replace('data:image/png;base64,', '', $firma_data);
        $firma_data = str_replace(' ', '+', $firma_data);
        $firma_decodificada = base64_decode($firma_data);
        
        if ($firma_decodificada === false) {
            throw new Exception("Error al decodificar los datos de la firma");
        }
        
        $nombre_firma = 'firma_' . $id . '_' . time() . '.png';
        $ruta_firma = $upload_dir . $nombre_firma;
        
        if (file_put_contents($ruta_firma, $firma_decodificada) === false) {
            throw new Exception("Error al guardar el archivo de firma");
        }
        
        if (!file_exists($ruta_firma)) {
            throw new Exception("El archivo de firma no se encontró después de guardarlo");
        }
        
        return $nombre_firma;
    }
    
    throw new Exception("Formato de firma inválido");
}

// Agregar validación previa
function validarDatosRequeridos($datos) {
    $camposRequeridos = [
        // Datos básicos
        'nombre_completo' => 'Nombre completo',
        'fecha_nacimiento' => 'Fecha de nacimiento',
        'email' => 'Email',
        'telefono' => 'Teléfono',
        'genero' => 'Género',
        
        // Documentación
        'rfc' => 'RFC',
        'curp' => 'CURP',
        'estado_civil' => 'Estado civil',
        'tipo_identificacion' => 'Tipo de identificación',
        'no_identificacion' => 'Número de identificación',
        
        // Dirección
        'direccion' => 'Dirección',
        'ciudad' => 'Ciudad',
        'estado' => 'Estado',
        'codigo_postal' => 'Código postal',
        
        // Referencias
        'nombre_ref1' => 'Nombre de referencia 1',
        'direccion_ref1' => 'Dirección de referencia 1',
        'telefono_ref1' => 'Teléfono de referencia 1',
        'parentesco_ref1' => 'Parentesco de referencia 1',
        'nombre_ref2' => 'Nombre de referencia 2',
        'direccion_ref2' => 'Dirección de referencia 2',
        'telefono_ref2' => 'Teléfono de referencia 2',
        'parentesco_ref2' => 'Parentesco de referencia 2',
        
        // Datos financieros
        'ingresos_mensuales' => 'Ingresos mensuales',
        'gastos_alimentacion' => 'Gastos en alimentación',
        'gastos_servicios' => 'Gastos en servicios',
        'gastos_transporte' => 'Gastos en transporte'
    ];

    $camposFaltantes = [];
    foreach ($camposRequeridos as $campo => $nombre) {
        if (!isset($datos[$campo]) || empty(trim($datos[$campo]))) {
            $camposFaltantes[] = $nombre;
        }
    }

    if (!empty($camposFaltantes)) {
        throw new Exception("Los siguientes campos son requeridos: " . implode(", ", $camposFaltantes));
    }
}

// Función para validar montos numéricos
function validarMontos($datos) {
    $camposNumericos = [
        'ingresos_mensuales',
        'otros_ingresos',
        'renta_mensual',
        'pago_auto',
        'gastos_alimentacion',
        'gastos_servicios',
        'gastos_transporte',
        'gastos_educacion',
        'deudas_creditos',
        'otros_gastos'
    ];

    foreach ($camposNumericos as $campo) {
        if (isset($datos[$campo]) && !is_numeric($datos[$campo])) {
            throw new Exception("El campo {$campo} debe ser un valor numérico");
        }
        if (isset($datos[$campo]) && floatval($datos[$campo]) < 0) {
            throw new Exception("El campo {$campo} no puede ser negativo");
        }
    }
}
?>