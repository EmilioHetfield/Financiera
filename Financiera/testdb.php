<?php
try {
    $conn = new PDO("mysql:host=mariadb;dbname=droopyst_testFinanciera", "root", "rootpass");
    echo "✅ Conexión exitosa a la base de datos";
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}
