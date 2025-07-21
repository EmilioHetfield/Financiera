-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 21-07-2025 a las 01:08:33
-- Versión del servidor: 10.6.22-MariaDB
-- Versión de PHP: 8.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `droopyst_testFinanciera`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autorizaciones`
--

CREATE TABLE `autorizaciones` (
  `id` bigint(20) NOT NULL,
  `prestamo_id` bigint(20) NOT NULL,
  `autorizador_id` bigint(20) NOT NULL,
  `fecha_autorizacion` date NOT NULL,
  `estado` enum('Pendiente','Aprobado','Rechazado') NOT NULL DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` bigint(20) NOT NULL,
  `nombre_completo` varchar(255) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `genero` char(1) NOT NULL,
  `id_vendedor` bigint(20) DEFAULT NULL,
  `ruta_firma` varchar(255) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre_completo`, `fecha_nacimiento`, `email`, `telefono`, `genero`, `id_vendedor`, `ruta_firma`, `estado`) VALUES
(1, 'Eduardo Brandon Flores Ramirez', '1992-02-08', 'ejemplo@ejemplo.com', '2313215656', 'M', 4, 'firma_1_1734149412.png', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `condiciones_vivienda`
--

CREATE TABLE `condiciones_vivienda` (
  `id` bigint(20) NOT NULL,
  `id_cliente` bigint(20) NOT NULL,
  `internet` enum('Si','No') NOT NULL,
  `telefono_fijo` enum('Si','No') NOT NULL,
  `telefono_movil` enum('Si','No') NOT NULL,
  `refrigerador` enum('Si','No') NOT NULL,
  `luz_electrica` enum('Si','No') NOT NULL,
  `agua_potable` enum('Si','No') NOT NULL,
  `auto_propio` enum('Si','No') NOT NULL,
  `tv_cable` enum('Si','No') NOT NULL,
  `alumbrado_publico` enum('Si','No') NOT NULL,
  `estufa` enum('Si','No') NOT NULL,
  `gas` enum('Si','No') NOT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `condiciones_vivienda`
--

INSERT INTO `condiciones_vivienda` (`id`, `id_cliente`, `internet`, `telefono_fijo`, `telefono_movil`, `refrigerador`, `luz_electrica`, `agua_potable`, `auto_propio`, `tv_cable`, `alumbrado_publico`, `estufa`, `gas`, `observaciones`) VALUES
(1, 1, 'Si', 'No', 'Si', 'Si', 'Si', 'Si', 'No', 'No', 'Si', 'Si', 'Si', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_financieros`
--

CREATE TABLE `datos_financieros` (
  `id` bigint(20) NOT NULL,
  `id_cliente` bigint(20) NOT NULL,
  `ingresos_mensuales` decimal(10,2) NOT NULL,
  `gastos_mensuales` decimal(10,2) NOT NULL,
  `otros_ingresos` decimal(10,2) DEFAULT NULL,
  `fuente_otros_ingresos` varchar(255) DEFAULT NULL,
  `renta_mensual` decimal(10,2) DEFAULT NULL,
  `pago_auto` decimal(10,2) DEFAULT NULL,
  `gastos_alimentacion` decimal(10,2) DEFAULT NULL,
  `gastos_servicios` decimal(10,2) DEFAULT NULL,
  `gastos_transporte` decimal(10,2) DEFAULT NULL,
  `gastos_educacion` decimal(10,2) DEFAULT NULL,
  `deudas_creditos` decimal(10,2) DEFAULT NULL,
  `otros_gastos` decimal(10,2) DEFAULT NULL,
  `descripcion_otros_gastos` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `datos_financieros`
--

INSERT INTO `datos_financieros` (`id`, `id_cliente`, `ingresos_mensuales`, `gastos_mensuales`, `otros_ingresos`, `fuente_otros_ingresos`, `renta_mensual`, `pago_auto`, `gastos_alimentacion`, `gastos_servicios`, `gastos_transporte`, `gastos_educacion`, `deudas_creditos`, `otros_gastos`, `descripcion_otros_gastos`) VALUES
(1, 1, 17000.00, 10105.00, 5000.00, 'Freelancer', 2600.00, 0.00, 4000.00, 105.00, 600.00, 2800.00, 0.00, 0.00, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_laborales`
--

CREATE TABLE `datos_laborales` (
  `id` bigint(20) NOT NULL,
  `id_cliente` bigint(20) NOT NULL,
  `tipo_empleo` enum('Tiempo completo','Medio tiempo','Ama de casa','Desempleado','Negocio propio','Retirado','Informal') NOT NULL,
  `ocupacion` enum('Empleado','Comerciante','Ventas','Obrero','Ama de casa','Construccion','Empleada domestica','Taxista','Servicios','Otro') NOT NULL,
  `nombre_empresa` varchar(255) DEFAULT NULL,
  `periodicidad_ingresos` enum('Mensual','Quincenal','Semanal','Otro') NOT NULL,
  `antiguedad_anos` int(11) NOT NULL,
  `antiguedad_meses` int(11) NOT NULL,
  `direccion` text NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `extension` varchar(10) DEFAULT NULL,
  `codigo_postal` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `datos_laborales`
--

INSERT INTO `datos_laborales` (`id`, `id_cliente`, `tipo_empleo`, `ocupacion`, `nombre_empresa`, `periodicidad_ingresos`, `antiguedad_anos`, `antiguedad_meses`, `direccion`, `telefono`, `extension`, `codigo_postal`) VALUES
(1, 1, 'Tiempo completo', 'Empleado', 'Grupo Tribuna', 'Mensual', 2, 6, 'La paz', '1234567891', '33', '78150');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_personales`
--

CREATE TABLE `datos_personales` (
  `id` bigint(20) NOT NULL,
  `id_cliente` bigint(20) NOT NULL,
  `rfc` varchar(13) NOT NULL,
  `curp` varchar(18) NOT NULL,
  `estado_civil` enum('Soltero','Casado','Divorciado','Viudo','Union Libre') NOT NULL,
  `dependientes_economicos` int(11) NOT NULL,
  `tipo_identificacion` enum('INE','Pasaporte','Cartilla','Licencia de conducir') NOT NULL,
  `no_identificacion` varchar(50) NOT NULL,
  `lugar_nacimiento` varchar(100) NOT NULL,
  `pais` varchar(50) NOT NULL,
  `tipo_vivienda` enum('Rentada','Propia','Vive con parientes') NOT NULL,
  `tiempo_vivienda` int(11) NOT NULL COMMENT 'Tiempo en meses',
  `nombre_conyuge` varchar(255) DEFAULT NULL,
  `fecha_nac_conyuge` date DEFAULT NULL,
  `telefono_conyuge` varchar(15) DEFAULT NULL,
  `ocupacion_conyuge` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `datos_personales`
--

INSERT INTO `datos_personales` (`id`, `id_cliente`, `rfc`, `curp`, `estado_civil`, `dependientes_economicos`, `tipo_identificacion`, `no_identificacion`, `lugar_nacimiento`, `pais`, `tipo_vivienda`, `tiempo_vivienda`, `nombre_conyuge`, `fecha_nac_conyuge`, `telefono_conyuge`, `ocupacion_conyuge`) VALUES
(1, 1, 'FORE920208EC5', 'FORE920208HPLLMD05', 'Casado', 1, 'INE', '32132453121asdasdas', '', 'México', 'Rentada', 26, 'Diana Flores Mendoza', '1993-10-03', '2224809995', 'Comerciante');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dependientes`
--

CREATE TABLE `dependientes` (
  `id` bigint(20) NOT NULL,
  `id_datos_personales` bigint(20) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `parentesco` varchar(50) NOT NULL,
  `ocupacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dependientes`
--

INSERT INTO `dependientes` (`id`, `id_datos_personales`, `nombre`, `parentesco`, `ocupacion`) VALUES
(1, 1, 'Liam Aaron Flores Flores', 'Hijo/a', 'Estudiante'),
(2, 1, 'Alondra Itzayana Ramirez Flores', 'Hijo/a', 'Estudiante');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `id` bigint(20) NOT NULL,
  `usuario_id` bigint(20) DEFAULT NULL,
  `cliente_id` bigint(20) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`id`, `usuario_id`, `cliente_id`, `direccion`, `ciudad`, `estado`, `codigo_postal`) VALUES
(1, NULL, 1, 'falsa', 'puebla', 'puebla', '72150');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  `descripcion` text DEFAULT NULL,
  `tipo_documento` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documentos`
--

INSERT INTO `documentos` (`id`, `cliente_id`, `nombre_archivo`, `ruta`, `fecha_subida`, `descripcion`, `tipo_documento`) VALUES
(2, 1, '17373172335967732885971982548078.jpg', 'uploads/documentos/1/678d5b91e9d56_20250119.jpg', '2025-01-19 20:07:45', 'Hoja', 'ine');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_prestamos`
--

CREATE TABLE `log_prestamos` (
  `id` int(11) NOT NULL,
  `prestamo_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `detalles` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagares`
--

CREATE TABLE `pagares` (
  `id` bigint(20) NOT NULL,
  `prestamo_id` bigint(20) NOT NULL,
  `tipo_pagare` enum('normal','con_aval') NOT NULL,
  `nombre_cliente` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `fecha_limite_pago` date NOT NULL,
  `ruta_firma_cliente` varchar(255) NOT NULL,
  `nombre_aval` varchar(255) DEFAULT NULL,
  `direccion_aval` varchar(255) DEFAULT NULL,
  `telefono_aval` varchar(20) DEFAULT NULL,
  `ruta_firma_aval` varchar(255) DEFAULT NULL,
  `multa` decimal(10,2) DEFAULT 0.00,
  `estado` enum('Pendiente','Pagado','Vencido') NOT NULL DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagares`
--

INSERT INTO `pagares` (`id`, `prestamo_id`, `tipo_pagare`, `nombre_cliente`, `monto`, `fecha`, `fecha_limite_pago`, `ruta_firma_cliente`, `nombre_aval`, `direccion_aval`, `telefono_aval`, `ruta_firma_aval`, `multa`, `estado`) VALUES
(1, 1, 'con_aval', 'Eduardo Brandon Flores Ramirez', 20000.00, '2024-12-13', '2025-05-30', 'uploads/firmas/firma_cliente_1734149818_675d06bae60c5.png', 'Emilio Mendoza', 'Falsa', '1234567894', 'uploads/firmas/firma_aval_1734149818_675d06bae6188.png', 0.00, 'Pendiente'),
(2, 2, 'normal', 'Eduardo Brandon Flores Ramirez', 20000.00, '2024-12-20', '2025-03-14', 'uploads/firmas/firma_cliente_1734754170_67663f7ab49da.png', NULL, NULL, NULL, NULL, 0.00, 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` bigint(20) NOT NULL,
  `pagare_id` bigint(20) NOT NULL,
  `tipo_pago` enum('preferente','parcial','total') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `observaciones` text DEFAULT NULL,
  `registrado_por` bigint(20) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `pagare_id`, `tipo_pago`, `monto`, `fecha_pago`, `observaciones`, `registrado_por`, `fecha_registro`) VALUES
(1, 1, 'preferente', 833.33, '2024-12-13', 'este es un ejemplo', 4, '2024-12-14 04:18:10'),
(2, 1, 'preferente', 25000.00, '2024-12-13', 'casi finalizado', 4, '2024-12-14 04:18:39'),
(3, 2, 'preferente', 100.00, '2025-01-15', '', 4, '2025-01-16 01:42:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id` bigint(20) NOT NULL,
  `cliente_id` bigint(20) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `plazo` int(11) NOT NULL,
  `tasa_interes` decimal(5,2) NOT NULL,
  `estado` enum('Pendiente','Completado') NOT NULL DEFAULT 'Pendiente',
  `ruta_firma_prestamo` varchar(255) NOT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_solicitud` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `monto_autorizado` decimal(10,2) DEFAULT NULL,
  `plazo_semanas` int(11) DEFAULT NULL,
  `fecha_autorizacion` datetime DEFAULT NULL,
  `fecha_primer_pago` date DEFAULT NULL,
  `fecha_ultimo_pago` date DEFAULT NULL,
  `autorizado_por` bigint(20) DEFAULT NULL,
  `monto_total` decimal(10,2) GENERATED ALWAYS AS (`monto_autorizado` + `monto_autorizado` * `tasa_interes` / 100) STORED,
  `pago_por_periodo` decimal(10,2) GENERATED ALWAYS AS (case when `frecuencia_pago` = 'quincenal' then `monto_total` / (`plazo_semanas` / 2) else `monto_total` / `plazo_semanas` end) STORED,
  `saldo_restante` decimal(10,2) DEFAULT NULL,
  `frecuencia_pago` enum('semanal','quincenal') NOT NULL DEFAULT 'semanal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id`, `cliente_id`, `monto`, `plazo`, `tasa_interes`, `estado`, `ruta_firma_prestamo`, `fecha_solicitud`, `estado_solicitud`, `monto_autorizado`, `plazo_semanas`, `fecha_autorizacion`, `fecha_primer_pago`, `fecha_ultimo_pago`, `autorizado_por`, `saldo_restante`, `frecuencia_pago`) VALUES
(1, 1, 26000.00, 6, 30.00, 'Completado', 'firma_1734149412_675d0524893b4_1734149412.png', '2024-12-14 04:10:12', 'aprobado', 20000.00, 24, '2024-12-14 04:14:16', '2024-12-23', '2025-06-02', 3, 0.00, 'semanal'),
(2, 1, 20000.00, 24, 40.00, '', '../firmas/firma_cliente_1.png', '2024-12-21 04:07:39', 'aprobado', 20000.00, 12, '2024-12-21 04:08:46', '2024-12-23', '2025-03-10', 3, 27900.00, 'semanal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `referencias`
--

CREATE TABLE `referencias` (
  `id` bigint(20) NOT NULL,
  `id_datos_personales` bigint(20) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `direccion` text NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `parentesco` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `referencias`
--

INSERT INTO `referencias` (`id`, `id_datos_personales`, `nombre`, `direccion`, `telefono`, `parentesco`) VALUES
(1, 1, 'Sergio Fabian Flores', 'La Paz', '2134657894', 'Amigo'),
(2, 1, 'Ricardo Cardenaz Lopez', 'Tlaxcala', '1245675314', 'Amigo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint(20) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `usuario` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tipo_usuario` enum('vendedor','autorizador','master') NOT NULL,
  `profile_image` varchar(255) DEFAULT 'assets/img/profile-img.jpg',
  `telefono` varchar(20) DEFAULT NULL,
  `ultima_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `tipo_usuario`, `profile_image`, `telefono`, `ultima_actualizacion`) VALUES
(1, 'Eduardo Brandon Flores Ramirez', 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'master', 'assets/img/profile/profile_1_1733167128.png', '2211125677', NULL),
(2, 'Juan Vendedor', 'vendedor', 'a60c36fc7c825e68bb5371a0e08f828a', 'vendedor', 'assets/img/profile-img.jpg', NULL, NULL),
(3, 'Pedro Autorizador', 'autorizador', 'e10adc3949ba59abbe56e057f20f883e', 'autorizador', 'assets/img/profile/profile_3_1733356655.jpg', NULL, NULL),
(4, 'Liam Aaron Flores Flores', 'liamaron', 'e10adc3949ba59abbe56e057f20f883e', 'vendedor', 'assets/img/profile/profile_4_1733356673.jpg', '2224809995', '2024-12-02 10:31:59'),
(5, 'vendedor2', 'vendedor@ejemplo.mx', 'a60c36fc7c825e68bb5371a0e08f828a', 'vendedor', 'assets/img/profile-img.jpg', '2211125677', '2024-12-02 10:32:15');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `autorizaciones`
--
ALTER TABLE `autorizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prestamo_id` (`prestamo_id`),
  ADD KEY `autorizador_id` (`autorizador_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vendedor` (`id_vendedor`);

--
-- Indices de la tabla `condiciones_vivienda`
--
ALTER TABLE `condiciones_vivienda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `datos_financieros`
--
ALTER TABLE `datos_financieros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `datos_laborales`
--
ALTER TABLE `datos_laborales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `datos_personales`
--
ALTER TABLE `datos_personales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `dependientes`
--
ALTER TABLE `dependientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_datos_personales` (`id_datos_personales`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `log_prestamos`
--
ALTER TABLE `log_prestamos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagares`
--
ALTER TABLE `pagares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prestamo_id` (`prestamo_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pagare_id` (`pagare_id`),
  ADD KEY `idx_registrado_por` (`registrado_por`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `referencias`
--
ALTER TABLE `referencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_datos_personales` (`id_datos_personales`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `autorizaciones`
--
ALTER TABLE `autorizaciones`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `condiciones_vivienda`
--
ALTER TABLE `condiciones_vivienda`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `datos_financieros`
--
ALTER TABLE `datos_financieros`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `datos_laborales`
--
ALTER TABLE `datos_laborales`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `datos_personales`
--
ALTER TABLE `datos_personales`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `dependientes`
--
ALTER TABLE `dependientes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `log_prestamos`
--
ALTER TABLE `log_prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagares`
--
ALTER TABLE `pagares`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `referencias`
--
ALTER TABLE `referencias`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `autorizaciones`
--
ALTER TABLE `autorizaciones`
  ADD CONSTRAINT `autorizaciones_ibfk_1` FOREIGN KEY (`prestamo_id`) REFERENCES `prestamos` (`id`),
  ADD CONSTRAINT `autorizaciones_ibfk_2` FOREIGN KEY (`autorizador_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_cliente_vendedor` FOREIGN KEY (`id_vendedor`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `condiciones_vivienda`
--
ALTER TABLE `condiciones_vivienda`
  ADD CONSTRAINT `condiciones_vivienda_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `datos_financieros`
--
ALTER TABLE `datos_financieros`
  ADD CONSTRAINT `datos_financieros_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `datos_laborales`
--
ALTER TABLE `datos_laborales`
  ADD CONSTRAINT `datos_laborales_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `datos_personales`
--
ALTER TABLE `datos_personales`
  ADD CONSTRAINT `datos_personales_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `dependientes`
--
ALTER TABLE `dependientes`
  ADD CONSTRAINT `dependientes_ibfk_1` FOREIGN KEY (`id_datos_personales`) REFERENCES `datos_personales` (`id`);

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `direcciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `direcciones_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `pagares`
--
ALTER TABLE `pagares`
  ADD CONSTRAINT `pagares_ibfk_1` FOREIGN KEY (`prestamo_id`) REFERENCES `prestamos` (`id`);

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `referencias`
--
ALTER TABLE `referencias`
  ADD CONSTRAINT `referencias_ibfk_1` FOREIGN KEY (`id_datos_personales`) REFERENCES `datos_personales` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
