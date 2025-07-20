<?php
function verificarAcceso($tipos_permitidos = []) {
    // Verificar si hay una sesión activa
    if (!isset($_SESSION['user'])) {
        header("Location: pages-login.html");
        exit();
    }

    // Si se especifican tipos permitidos, verificar el tipo de usuario
    if (!empty($tipos_permitidos)) {
        if (!in_array($_SESSION['user']['tipo_usuario'], $tipos_permitidos)) {
            header("Location: index.php");
            exit();
        }
    }
}
?>
