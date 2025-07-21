<?php
// Configuración de la base de datos
//  $servername = "localhost";
//  $username = "droopyst_test";
//  $password = "M3nd0z@2020.";
//  $dbname = "droopyst_testFinanciera";
 // Configuración de la base de datos
 define('DB_HOST', '172.17.0.4');
 define('DB_NAME', 'droopyst_testFinanciera');
 define('DB_USER', 'droopyst_test');
 define('DB_PASS', 'M3nd0z@2020.');
// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Intentar conectar a la base de datos
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Guardar la conexión en una variable global
    $GLOBALS['conn'] = $conn;
    
} catch(PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}

// Configuración de sesión
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Otras configuraciones globales
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
?>