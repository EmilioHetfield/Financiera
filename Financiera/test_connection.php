<?php
// Datos de conexión server
// $servername = "localhost";
// $username = "droopyst_test";
// $password = "M3nd0z@2020.";
// $dbname = "droopyst_testFinanciera";

// Datos de conexión local
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "financiera";

// Intentar conexión
try {
    // Establecer timeout más corto y opciones específicas
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3,  // 3 segundos de timeout
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_PERSISTENT => false // Desactivar conexiones persistentes
    );

    echo "Intentando conectar...<br>";
    
    // Intentar primero con el hostname
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
        echo "<div style='color: green'>¡Conexión exitosa con hostname!</div>";
    } catch (PDOException $e) {
        echo "Fallo con hostname, intentando con IP...<br>";
        // Si falla, intentar con localhost
        $conn = new PDO("mysql:host=localhost;dbname=$dbname", $username, $password, $options);
        echo "<div style='color: green'>¡Conexión exitosa con localhost!</div>";
    }
    
    // Si llegamos aquí, la conexión fue exitosa
    echo "<pre>";
    echo "Detalles de la conexión:<br>";
    echo "Host: $servername<br>";
    echo "Base de datos: $dbname<br>";
    echo "Usuario: $username<br>";
    
    // Mostrar versión de MySQL
    $version = $conn->query('select version()')->fetchColumn();
    echo "Versión de MySQL: $version<br>";
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>";
    echo "Error de conexión: " . $e->getMessage() . "<br>";
    echo "</div>";
    echo "<pre>";
    echo "Código de error: " . $e->getCode() . "<br>";
    echo "Detalles del intento de conexión:<br>";
    echo "Host: $servername<br>";
    echo "Base de datos: $dbname<br>";
    echo "Usuario: $username<br>";
    
    // Información adicional de debug
    echo "\nInformación adicional de debug:";
    echo "\nPHP Version: " . phpversion();
    echo "\nPDO Drivers disponibles: ";
    print_r(PDO::getAvailableDrivers());
    echo "</pre>";
}

// Cerrar la conexión
$conn = null;

// Información adicional de red
echo "<hr>";
echo "<h3>Diagnóstico de red:</h3>";
echo "<pre>";
echo "Intentando ping al servidor...<br>";
$ping = shell_exec("ping -n 1 " . $servername);
echo "Resultado del ping:<br>";
echo $ping;

echo "\nIntentando traceroute al servidor...<br>";
$tracert = shell_exec("tracert -h 15 " . $servername);
echo "Resultado del traceroute:<br>";
echo $tracert;
echo "</pre>";
?> 