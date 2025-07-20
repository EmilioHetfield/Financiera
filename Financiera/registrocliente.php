<?php
session_start();
require_once 'config.php';
require_once 'functions/dashboard_queries.php';
require_once 'functions/auth.php';
verificarAcceso(['master', 'vendedor']);

include 'views/head.php';
include 'views/header.php';
include 'views/nav_menu.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Clientes</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Registrar cliente</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Registro de Nuevo Cliente</h5>

                        <form id="registroClienteForm" class="needs-validation" novalidate>
                            <!-- Tabs de navegación -->
                            <ul class="nav nav-tabs mb-3" id="clienteTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="datos-basicos-tab" data-bs-toggle="tab" data-bs-target="#datos-basicos" type="button">Datos Básicos</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="documentacion-tab" data-bs-toggle="tab" data-bs-target="#documentacion" type="button">Documentación</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="datos-laborales-tab" data-bs-toggle="tab" data-bs-target="#datos-laborales" type="button">Datos Laborales</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="referencias-tab" data-bs-toggle="tab" data-bs-target="#referencias" type="button">Referencias</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="prestamo-tab" data-bs-toggle="tab" data-bs-target="#prestamo" type="button">Préstamo</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="clienteTabsContent">
                                <!-- Pestaña: Datos Básicos -->
                                <div class="tab-pane fade show active" id="datos-basicos">
                                    <!-- Datos personales -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nombre_completo" class="form-label">Nombre Completo *</label>
                                                <input type="text" class="form-control" id="nombre_completo"
                                                    name="nombre_completo" required>
                                                <div class="invalid-feedback">Por favor ingrese el nombre completo</div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                                                <input type="date" class="form-control" id="fecha_nacimiento"
                                                    name="fecha_nacimiento" required max="<?php echo date('Y-m-d'); ?>">
                                                <div class="invalid-feedback">Por favor seleccione la fecha de nacimiento</div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" class="form-control" id="email" name="email" required>
                                                <div class="invalid-feedback">Por favor ingrese un email válido</div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telefono" class="form-label">Teléfono *</label>
                                                <input type="tel" class="form-control" id="telefono" name="telefono" required
                                                    pattern="[0-9]{10}" maxlength="10">
                                                <div class="invalid-feedback">Por favor ingrese un teléfono válido (10 dígitos)
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="genero" class="form-label">Género *</label>
                                                <select class="form-select" id="genero" name="genero" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="M">Masculino</option>
                                                    <option value="F">Femenino</option>
                                                </select>
                                                <div class="invalid-feedback">Por favor seleccione el género</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dirección -->
                                    <div class="row mt-4">
                                        <h5>Dirección</h5>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="direccion" class="form-label">Calle y Número *</label>
                                                <input type="text" class="form-control" id="direccion" name="direccion"
                                                    required>
                                                <div class="invalid-feedback">Por favor ingrese la dirección</div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="ciudad" class="form-label">Ciudad *</label>
                                                <input type="text" class="form-control" id="ciudad" name="ciudad" required>
                                                <div class="invalid-feedback">Por favor ingrese la ciudad</div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="estado" class="form-label">Estado *</label>
                                                <input type="text" class="form-control" id="estado" name="estado" required>
                                                <div class="invalid-feedback">Por favor ingrese el estado</div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="codigo_postal" class="form-label">Código Postal *</label>
                                                <input type="text" class="form-control" id="codigo_postal" name="codigo_postal"
                                                    required pattern="[0-9]{5}" maxlength="5">
                                                <div class="invalid-feedback">Por favor ingrese un código postal válido (5
                                                    dígitos)</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Agregar campos adicionales -->
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="estado_civil" class="form-label">Estado Civil *</label>
                                                <select class="form-select" id="estado_civil" name="estado_civil" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="Soltero">Soltero</option>
                                                    <option value="Casado">Casado</option>
                                                    <option value="Divorciado">Divorciado</option>
                                                    <option value="Viudo">Viudo</option>
                                                    <option value="Union Libre">Unión Libre</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="tipo_vivienda" class="form-label">Tipo de Vivienda *</label>
                                                <select class="form-select" id="tipo_vivienda" name="tipo_vivienda" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="Propia">Propia</option>
                                                    <option value="Rentada">Rentada</option>
                                                    <option value="Familiar">Vive con parientes</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tiempo_vivienda" class="form-label">Tiempo en el Domicilio (meses) *</label>
                                                <input type="number" class="form-control" id="tiempo_vivienda" name="tiempo_vivienda" required min="0">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="dependientes" class="form-label">Dependientes Económicos *</label>
                                                <input type="number" class="form-control" id="dependientes" name="dependientes" required min="0" step="1">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3" id="seccion_conyuge" style="display: none;">
                                        <h5>Datos del Cónyuge</h5>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nombre_conyuge" class="form-label">Nombre Completo del Cónyuge *</label>
                                                <input type="text" class="form-control" id="nombre_conyuge" name="nombre_conyuge">
                                            </div>
                                            <div class="mb-3">
                                                <label for="fecha_nac_conyuge" class="form-label">Fecha de Nacimiento del Cónyuge</label>
                                                <input type="date" class="form-control" id="fecha_nac_conyuge" name="fecha_nac_conyuge">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telefono_conyuge" class="form-label">Teléfono del Cónyuge</label>
                                                <input type="tel" class="form-control" id="telefono_conyuge" name="telefono_conyuge" pattern="[0-9]{10}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="ocupacion_conyuge" class="form-label">Ocupación del Cónyuge</label>
                                                <input type="text" class="form-control" id="ocupacion_conyuge" name="ocupacion_conyuge">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3" id="seccion_dependientes" style="display: none;">
                                        <h5>Datos de Dependientes</h5>
                                        <div id="contenedor_dependientes">
                                            <!-- Los campos de dependientes se agregarán dinámicamente aquí -->
                                        </div>
                                    </div>

                                    <!-- Agregar nueva sección en la pestaña de Datos Básicos -->
                                    <div class="row mt-4">
                                        <h5>Condiciones de Vivienda</h5>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Internet</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="internet" id="internet_si" value="Si" required>
                                                    <label class="form-check-label" for="internet_si">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="internet" id="internet_no" value="No">
                                                    <label class="form-check-label" for="internet_no">No</label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Teléfono Fijo</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="telefono_fijo" id="telefono_fijo_si" value="Si" required>
                                                    <label class="form-check-label" for="telefono_fijo_si">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="telefono_fijo" id="telefono_fijo_no" value="No">
                                                    <label class="form-check-label" for="telefono_fijo_no">No</label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Teléfono Móvil</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="telefono_movil" id="telefono_movil_si" value="Si" required>
                                                    <label class="form-check-label" for="telefono_movil_si">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="telefono_movil" id="telefono_movil_no" value="No">
                                                    <label class="form-check-label" for="telefono_movil_no">No</label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Refrigerador</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="refrigerador" id="refrigerador_si" value="Si" required>
                                                    <label class="form-check-label" for="refrigerador_si">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="refrigerador" id="refrigerador_no" value="No">
                                                    <label class="form-check-label" for="refrigerador_no">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Luz Eléctrica</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="luz_electrica" id="luz_electrica_si" value="Si" required>
                                                    <label class="form-check-label" for="luz_electrica_si">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="luz_electrica" id="luz_electrica_no" value="No">
                                                    <label class="form-check-label" for="luz_electrica_no">No</label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Agua Potable</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="agua_potable" id="agua_potable_si" value="Si" required>
                                                    <label class="form-check-label" for="agua_potable_si">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="agua_potable" id="agua_potable_no" value="No">
                                                    <label class="form-check-label" for="agua_potable_no">No</label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Auto Propio</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="auto_propio" id="auto_propio_si" value="Si" required>
                                                    <label class="form-check-label" for="auto_propio_si">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="auto_propio" id="auto_propio_no" value="No">
                                                    <label class="form-check-label" for="auto_propio_no">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">TV por Cable</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tv_cable" id="tv_cable_si" value="Si" required>
                                                    <label class="form-check-label" for="tv_cable_si">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tv_cable" id="tv_cable_no" value="No">
                                                    <label class="form-check-label" for="tv_cable_no">No</label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Alumbrado Público</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="alumbrado_publico" id="alumbrado_publico_si" value="Si" required>
                                                    <label class="form-check-label" for="alumbrado_publico_si">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="alumbrado_publico" id="alumbrado_publico_no" value="No">
                                                    <label class="form-check-label" for="alumbrado_publico_no">No</label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Estufa</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="estufa" id="estufa_si" value="Si" required>
                                                    <label class="form-check-label" for="estufa_si">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="estufa" id="estufa_no" value="No">
                                                    <label class="form-check-label" for="estufa_no">No</label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Gas</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="gas" id="gas_si" value="Si" required>
                                                    <label class="form-check-label" for="gas_si">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="gas" id="gas_no" value="No">
                                                    <label class="form-check-label" for="gas_no">No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="observaciones" class="form-label">Observaciones</label>
                                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pestaña: Documentación -->
                                <div class="tab-pane fade" id="documentacion">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="rfc" class="form-label">RFC *</label>
                                                <input type="text" class="form-control" id="rfc" name="rfc" required maxlength="13">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="curp" class="form-label">CURP *</label>
                                                <input type="text" class="form-control" id="curp" name="curp" required maxlength="18">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tipo_identificacion" class="form-label">Tipo de Identificación *</label>
                                                <select class="form-select" id="tipo_identificacion" name="tipo_identificacion" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="INE">INE</option>
                                                    <option value="Pasaporte">Pasaporte</option>
                                                    <option value="Cartilla">Cartilla</option>
                                                    <option value="Licencia">Licencia de conducir</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="no_identificacion" class="form-label">Número de Identificación *</label>
                                                <input type="text" class="form-control" id="no_identificacion" name="no_identificacion" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pestaña: Datos Laborales -->
                                <div class="tab-pane fade" id="datos-laborales">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tipo_empleo" class="form-label">Tipo de Empleo *</label>
                                                <select class="form-select" id="tipo_empleo" name="tipo_empleo" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="Tiempo completo">Tiempo completo</option>
                                                    <option value="Medio tiempo">Medio tiempo</option>
                                                    <option value="Ama de casa">Ama de casa</option>
                                                    <option value="Negocio propio">Negocio propio</option>
                                                    <option value="Informal">Informal</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="ocupacion" class="form-label">Ocupación *</label>
                                                <select class="form-select" id="ocupacion" name="ocupacion" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="Empleado">Empleado</option>
                                                    <option value="Comerciante">Comerciante</option>
                                                    <option value="Obrero">Obrero</option>
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="periodicidad_ingresos" class="form-label">Periodicidad de Ingresos *</label>
                                                <select class="form-select" id="periodicidad_ingresos" name="periodicidad_ingresos" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="Mensual">Mensual</option>
                                                    <option value="Quincenal">Quincenal</option>
                                                    <option value="Semanal">Semanal</option>
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="antiguedad_anos" class="form-label">Años de Antigüedad *</label>
                                                <input type="number" class="form-control" id="antiguedad_anos" name="antiguedad_anos" min="0" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="antiguedad_meses" class="form-label">Meses de Antigüedad *</label>
                                                <input type="number" class="form-control" id="antiguedad_meses" name="antiguedad_meses" min="0" max="11" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Nueva sección para datos de la empresa -->
                                    <div class="row mt-4">
                                        <h5>Datos de la Empresa</h5>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nombre_empresa" class="form-label">Nombre de la Empresa *</label>
                                                <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="direccion_empresa" class="form-label">Dirección de la Empresa *</label>
                                                <textarea class="form-control" id="direccion_empresa" name="direccion_empresa" required rows="3"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label for="telefono_empresa" class="form-label">Teléfono de la Empresa *</label>
                                                        <input type="tel" class="form-control" id="telefono_empresa" name="telefono_empresa" required pattern="[0-9]{10}">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="extension" class="form-label">Extensión</label>
                                                        <input type="text" class="form-control" id="extension" name="extension" maxlength="10">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="codigo_postal_empresa" class="form-label">Código Postal *</label>
                                                <input type="text" class="form-control" id="codigo_postal_empresa" name="codigo_postal_empresa" required pattern="[0-9]{5}" maxlength="5">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Agregar dentro de la pestaña de Datos Laborales -->
                                    <div class="row mt-4">
                                        <h5>Información Financiera</h5>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ingresos_mensuales" class="form-label">Ingresos Mensuales *</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="ingresos_mensuales" 
                                                       name="ingresos_mensuales" required data-type="currency">
                                            </div>
                                            <div class="mb-3">
                                                <label for="otros_ingresos" class="form-label">Otros Ingresos</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="otros_ingresos" 
                                                       name="otros_ingresos">
                                            </div>
                                            <div class="mb-3">
                                                <label for="fuente_otros_ingresos" class="form-label">Fuente de Otros Ingresos</label>
                                                <input type="text" class="form-control" id="fuente_otros_ingresos" 
                                                       name="fuente_otros_ingresos">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <h6>Gastos Mensuales</h6>
                                            <div class="mb-3">
                                                <label for="gastos_alimentacion" class="form-label">Alimentación *</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="gastos_alimentacion" 
                                                       name="gastos_alimentacion" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="gastos_servicios" class="form-label">Servicios (Luz, Agua, etc.) *</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="gastos_servicios" 
                                                       name="gastos_servicios" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="gastos_transporte" class="form-label">Transporte *</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="gastos_transporte" 
                                                       name="gastos_transporte" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="renta_mensual" class="form-label">Renta/Hipoteca</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="renta_mensual" 
                                                       name="renta_mensual">
                                            </div>
                                            <div class="mb-3">
                                                <label for="pago_auto" class="form-label">Pago de Auto</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="pago_auto" 
                                                       name="pago_auto">
                                            </div>
                                            <div class="mb-3">
                                                <label for="gastos_educacion" class="form-label">Educación</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="gastos_educacion" 
                                                       name="gastos_educacion">
                                            </div>
                                            <div class="mb-3">
                                                <label for="deudas_creditos" class="form-label">Deudas/Créditos</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="deudas_creditos" 
                                                       name="deudas_creditos">
                                            </div>
                                            <div class="mb-3">
                                                <label for="otros_gastos" class="form-label">Otros Gastos</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="otros_gastos" 
                                                       name="otros_gastos">
                                            </div>
                                            <div class="mb-3">
                                                <label for="descripcion_otros_gastos" class="form-label">Descripción de Otros Gastos</label>
                                                <input type="text" class="form-control" id="descripcion_otros_gastos" 
                                                       name="descripcion_otros_gastos">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pestaña: Referencias -->
                                <div class="tab-pane fade" id="referencias">
                                    <h5>Primera Referencia</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nombre_ref1" class="form-label">Nombre Completo *</label>
                                                <input type="text" class="form-control" id="nombre_ref1" name="nombre_ref1" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="direccion_ref1" class="form-label">Dirección *</label>
                                                <input type="text" class="form-control" id="direccion_ref1" name="direccion_ref1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telefono_ref1" class="form-label">Teléfono *</label>
                                                <input type="tel" class="form-control" id="telefono_ref1" name="telefono_ref1" required pattern="[0-9]{10}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="parentesco_ref1" class="form-label">Parentesco *</label>
                                                <input type="text" class="form-control" id="parentesco_ref1" name="parentesco_ref1" required>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">Segunda Referencia</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nombre_ref2" class="form-label">Nombre Completo *</label>
                                                <input type="text" class="form-control" id="nombre_ref2" name="nombre_ref2" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="direccion_ref2" class="form-label">Dirección *</label>
                                                <input type="text" class="form-control" id="direccion_ref2" name="direccion_ref2" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telefono_ref2" class="form-label">Teléfono *</label>
                                                <input type="tel" class="form-control" id="telefono_ref2" name="telefono_ref2" required pattern="[0-9]{10}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="parentesco_ref2" class="form-label">Parentesco *</label>
                                                <input type="text" class="form-control" id="parentesco_ref2" name="parentesco_ref2" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pestaña: Préstamo -->
                                <div class="tab-pane fade" id="prestamo">
                                    <!-- Datos del préstamo -->
                                    <div class="row mt-4">
                                        <h5>Información del Préstamo</h5>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="monto" class="form-label">Monto del Préstamo *</label>
                                                <input type="number" step="0.01" class="form-control" id="monto" name="monto" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="plazo" class="form-label">Plazo (meses) *</label>
                                                <input type="number" min="1" class="form-control" id="plazo" name="plazo"
                                                    required>
                                                <div class="invalid-feedback">Por favor ingrese el plazo</div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="tasa_interes" class="form-label">Tasa de Interés (%) *</label>
                                                <input type="number" step="0.01" class="form-control" id="tasa_interes" name="tasa_interes" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="frecuencia_pago" class="form-label">Frecuencia de Pago *</label>
                                                <select class="form-select" id="frecuencia_pago" name="frecuencia_pago" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="semanal">Semanal</option>
                                                    <option value="quincenal">Quincenal</option>
                                                </select>
                                                <div class="invalid-feedback">Por favor seleccione la frecuencia de pago</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Agregar después del div de información del préstamo -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="alert alert-info" id="resumen-pagos">
                                                Complete los campos para ver el resumen de pagos
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Firma del cliente -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="firmaCanvas" class="form-label">Firma del Cliente *</label>
                                        <canvas id="firmaCanvas" 
                                                width="400" 
                                                height="200" 
                                                class="border rounded" 
                                                style="touch-action: none;">
                                        </canvas>
                                        <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="limpiarFirma()">
                                            <i class="bi bi-eraser"></i> Limpiar Firma
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Registrar Cliente
                                    </button>
                                    <a href="index.php" class="btn btn-secondary ms-2">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'views/footer.php'; ?>

<!-- Scripts específicos -->
<script src="assets/js/scriptcreacioncliente.js"></script>

<script>
// Validaciones adicionales del formulario
document.getElementById('telefono').addEventListener('input', function(e) {
    // Solo permitir números
    this.value = this.value.replace(/[^0-9]/g, '');
});

document.getElementById('codigo_postal').addEventListener('input', function(e) {
    // Solo permitir números
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Mostrar alerta de confirmación antes de cancelar
document.querySelector('a[href="index.php"]').addEventListener('click', function(e) {
    e.preventDefault();
    if (confirm('¿Está seguro de cancelar el registro? Los datos no guardados se perderán.')) {
        window.location.href = this.href;
    }
});

// Dentro del evento submit del formulario
document.getElementById('registroClienteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Mostrar loader o spinner
    document.getElementById('loader').style.display = 'block';
    
    fetch('functions/API_Functions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        console.log('Respuesta recibida:', data);
        
        if (data.success) {
            // Mostrar mensaje de éxito
            Swal.fire({
                icon: 'success',
                title: '¡Registro exitoso!',
                text: data.message,
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir a la página deseada
                    window.location.href = 'lista_clientes.php';
                }
            });
        } else {
            throw new Error(data.message || 'Error desconocido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Mostrar mensaje de error
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Ocurrió un error al procesar la solicitud',
            confirmButtonText: 'OK'
        });
    })
    .finally(() => {
        // Ocultar loader o spinner
        document.getElementById('loader').style.display = 'none';
    });
});

// Agregar estilos para el loader
const style = document.createElement('style');
style.textContent = `
    #loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

function calcularPagos() {
    const monto = parseFloat(document.getElementById('monto').value) || 0;
    const plazo = parseInt(document.getElementById('plazo').value) || 0;
    const tasaInteres = parseFloat(document.getElementById('tasa_interes').value) || 0;
    const frecuenciaPago = document.getElementById('frecuencia_pago').value;

    if (monto > 0 && plazo > 0 && tasaInteres > 0 && frecuenciaPago) {
        const interes = (monto * (tasaInteres / 100));
        const montoTotal = monto + interes;
        const semanasTotales = plazo * 4; // 4 semanas por mes aproximadamente
        
        let montoPago;
        if (frecuenciaPago === 'quincenal') {
            montoPago = montoTotal / (semanasTotales / 2);
        } else {
            montoPago = montoTotal / semanasTotales;
        }

        // Si quieres mostrar esta información al usuario
        const resumenPago = `
            Monto total a pagar: $${montoTotal.toFixed(2)}
            Pago ${frecuenciaPago}: $${montoPago.toFixed(2)}
            Total de pagos: ${frecuenciaPago === 'quincenal' ? semanasTotales/2 : semanasTotales}
        `;
        
        // Puedes mostrar esta información en algún elemento HTML
        if (document.getElementById('resumen-pagos')) {
            document.getElementById('resumen-pagos').textContent = resumenPago;
        }
    }
}

// Agregar event listeners para recalcular cuando cambien los valores
['monto', 'plazo', 'tasa_interes', 'frecuencia_pago'].forEach(id => {
    document.getElementById(id).addEventListener('change', calcularPagos);
});

// Agregar después del JavaScript existente
document.addEventListener('DOMContentLoaded', function() {
    // Validación de RFC
    document.getElementById('rfc').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Validación de CURP
    document.getElementById('curp').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Validación de teléfonos de referencias
    ['telefono_ref1', 'telefono_ref2'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    // Navegación entre tabs
    const triggerTabList = [].slice.call(document.querySelectorAll('#clienteTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });

    // Manejo de campos del cónyuge
    const estadoCivil = document.getElementById('estado_civil');
    const seccionConyuge = document.getElementById('seccion_conyuge');
    
    estadoCivil.addEventListener('change', function() {
        const mostrarConyuge = ['Casado', 'Union Libre'].includes(this.value);
        seccionConyuge.style.display = mostrarConyuge ? 'block' : 'none';
        
        // Actualizar required en campos del cónyuge
        const camposConyuge = seccionConyuge.querySelectorAll('input');
        camposConyuge.forEach(campo => {
            campo.required = mostrarConyuge;
        });
    });

    // Manejo de campos de dependientes
    const numeroDependientes = document.getElementById('dependientes');
    const seccionDependientes = document.getElementById('seccion_dependientes');
    const contenedorDependientes = document.getElementById('contenedor_dependientes');

    numeroDependientes.addEventListener('change', function() {
        const cantidad = parseInt(this.value) || 0;
        seccionDependientes.style.display = cantidad > 0 ? 'block' : 'none';
        generarCamposDependientes(cantidad);
    });

    function generarCamposDependientes(cantidad) {
        contenedorDependientes.innerHTML = ''; // Limpiar contenedor

        for (let i = 0; i < cantidad; i++) {
            const dependienteHTML = `
                <div class="row mb-3">
                    <h6>Dependiente ${i + 1}</h6>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre_dep_${i}" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre_dep_${i}" 
                                   name="dependientes[${i}][nombre]" required>
                        </div>
                        <div class="mb-3">
                            <label for="parentesco_dep_${i}" class="form-label">Parentesco *</label>
                            <select class="form-select" id="parentesco_dep_${i}" 
                                    name="dependientes[${i}][parentesco]" required>
                                <option value="">Seleccione...</option>
                                <option value="Hijo/a">Hijo/a</option>
                                <option value="Padre/Madre">Padre/Madre</option>
                                <option value="Hermano/a">Hermano/a</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ocupacion_dep_${i}" class="form-label">Ocupación</label>
                            <input type="text" class="form-control" id="ocupacion_dep_${i}" 
                                   name="dependientes[${i}][ocupacion]">
                        </div>
                    </div>
                </div>
            `;
            contenedorDependientes.insertAdjacentHTML('beforeend', dependienteHTML);
        }
    }

    // Validación de teléfono del cónyuge
    document.getElementById('telefono_conyuge')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Validaciones para campos de empresa
    document.getElementById('telefono_empresa')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('extension')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('codigo_postal_empresa')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    const form = document.getElementById('registroClienteForm');
    const submitButton = form.querySelector('button[type="submit"]');
    const tabs = document.querySelectorAll('.nav-link');
    const tabPanes = document.querySelectorAll('.tab-pane');

    // Función para verificar si una pestaña está completa
    function isTabComplete(tabPane) {
        const requiredFields = tabPane.querySelectorAll('[required]');
        const radioGroups = new Set();
        
        for (let field of requiredFields) {
            // Para grupos de radio buttons
            if (field.type === 'radio') {
                radioGroups.add(field.name);
                continue;
            }
            
            // Para otros campos
            if (!field.value.trim()) {
                return false;
            }
        }

        // Verificar grupos de radio buttons
        for (let groupName of radioGroups) {
            const checkedRadio = tabPane.querySelector(`input[name="${groupName}"]:checked`);
            if (!checkedRadio) {
                return false;
            }
        }

        return true;
    }

    // Función para verificar si todo el formulario está completo
    function isFormComplete() {
        // Verificar todas las pestañas
        for (let tabPane of tabPanes) {
            if (!isTabComplete(tabPane)) {
                return false;
            }
        }

        // Verificar la firma
        const canvas = document.getElementById('firmaCanvas');
        const context = canvas.getContext('2d');
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;
        
        // Verificar si el canvas está en blanco
        for (let i = 0; i < data.length; i += 4) {
            if (data[i + 3] !== 0) { // Si hay algún pixel no transparente
                return true;
            }
        }
        
        return false;
    }

    // Función para actualizar el estado del botón
    function updateSubmitButton() {
        const isComplete = isFormComplete();
        submitButton.disabled = !isComplete;
        
        // Actualizar el estilo del botón
        if (isComplete) {
            submitButton.classList.remove('btn-secondary');
            submitButton.classList.add('btn-primary');
            submitButton.title = 'Enviar formulario';
        } else {
            submitButton.classList.remove('btn-primary');
            submitButton.classList.add('btn-secondary');
            submitButton.title = 'Complete todos los campos requeridos';
        }
    }

    // Función para marcar las pestañas como completas/incompletas
    function updateTabStatus() {
        tabPanes.forEach((tabPane, index) => {
            const isComplete = isTabComplete(tabPane);
            const tab = tabs[index];
            
            if (isComplete) {
                tab.classList.add('text-success');
                tab.classList.remove('text-danger');
            } else {
                tab.classList.add('text-danger');
                tab.classList.remove('text-success');
            }
        });
    }

    // Agregar listeners para todos los campos del formulario
    form.addEventListener('input', function() {
        updateSubmitButton();
        updateTabStatus();
    });

    form.addEventListener('change', function() {
        updateSubmitButton();
        updateTabStatus();
    });

    // Agregar listener para la firma
    const canvas = document.getElementById('firmaCanvas');
    canvas.addEventListener('mouseup', updateSubmitButton);
    canvas.addEventListener('touchend', updateSubmitButton);

    // Inicializar estado del botón
    updateSubmitButton();
    updateTabStatus();

    // Mostrar tooltip en campos requeridos
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.title = 'Este campo es obligatorio';
    });

    // Agregar validación antes de enviar
    form.addEventListener('submit', function(e) {
        if (!isFormComplete()) {
            e.preventDefault();
            alert('Por favor complete todos los campos requeridos y la firma antes de enviar.');
            
            // Encontrar la primera pestaña incompleta y mostrarla
            for (let i = 0; i < tabPanes.length; i++) {
                if (!isTabComplete(tabPanes[i])) {
                    bootstrap.Tab.getInstance(tabs[i]).show();
                    break;
                }
            }
        }
    });

    // Configuración de datepickers
    const datepickers = document.querySelectorAll('input[type="date"]');
    datepickers.forEach(datepicker => {
        datepicker.addEventListener('keydown', function(e) {
            // Permitir teclas de navegación y números
            if (e.key.length === 1 && !/[\d-]/.test(e.key)) {
                e.preventDefault();
            }
        });

        // Validar formato de fecha
        datepicker.addEventListener('change', function() {
            const date = new Date(this.value);
            if (isNaN(date.getTime())) {
                this.setCustomValidity('Por favor ingrese una fecha válida');
            } else {
                this.setCustomValidity('');
            }
        });
    });

    // Configuración de campos numéricos
    const numericalInputs = document.querySelectorAll('input[type="number"]');
    numericalInputs.forEach(input => {
        // Permitir entrada manual
        input.addEventListener('keydown', function(e) {
            // Permitir: backspace, delete, tab, escape, enter, punto y números
            if ([46, 8, 9, 27, 13, 110, 190].indexOf(e.keyCode) !== -1 ||
                // Permitir: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode >= 35 && e.keyCode <= 39) || 
                // Permitir: home, end, left, right
                (e.ctrlKey === true && [65, 67, 86, 88].indexOf(e.keyCode) !== -1)) {
                return;
            }
            // Asegurar que sea un número
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && 
                (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

        // Validar valor mínimo y paso
        input.addEventListener('change', function() {
            const min = parseFloat(this.min || 0);
            const step = parseFloat(this.step || 1);
            let value = parseFloat(this.value || 0);

            // Asegurar valor mínimo
            if (value < min) {
                value = min;
            }

            // Ajustar al paso más cercano
            const steps = Math.round((value - min) / step);
            value = min + (steps * step);

            // Formatear a 2 decimales si es necesario
            if (this.step.includes('.')) {
                value = value.toFixed(2);
            }

            this.value = value;
        });
    });

    // Validación específica para campos monetarios
    const monetaryInputs = document.querySelectorAll('input[data-type="currency"]');
    monetaryInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^\d.]/g, '');
            
            // Permitir solo un punto decimal
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limitar a dos decimales
            if (parts.length === 2 && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
            }

            this.value = value;
        });
    });

    // Manejar el campo de dependientes
    const dependientesInput = document.getElementById('dependientes');
    if (dependientesInput) {
        dependientesInput.addEventListener('change', function() {
            let valor = parseInt(this.value) || 0;
            if (valor < 0) valor = 0;
            this.value = valor;
        });
    }

    // Asegurar que los campos de texto no sean arrays
    const textInputs = document.querySelectorAll('input[type="text"]');
    textInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (Array.isArray(this.value)) {
                this.value = this.value.join(', ');
            }
        });
    });
});
</script>

</body>

</html>