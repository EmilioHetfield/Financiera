<?php
// Asegurarse de que no haya salida antes de este archivo
ob_start();

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para verificar si el usuario está autenticado
function checkSession() {
    // Si no hay sesión activa, redirigir al login
    if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
        // Limpiar cualquier salida pendiente
        ob_clean();
        
        // Redirigir a la página de login
        header('Location: pages-login.html');
        exit();
    }

    // Verificar tiempo de inactividad (30 minutos)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        // Limpiar cualquier salida pendiente
        ob_clean();
        
        // Destruir la sesión
        session_unset();
        session_destroy();
        
        // Redirigir a la página de login
        header('Location: pages-login.html');
        exit();
    }

    // Actualizar tiempo de última actividad
    $_SESSION['last_activity'] = time();

    return true;
}

// Función para cerrar sesión
function logout() {
    // Limpiar cualquier salida pendiente
    ob_clean();
    
    // Destruir la sesión
    session_unset();
    session_destroy();
    
    // Redirigir a la página de login
    header('Location: pages-login.html');
    exit();
}

// Función para verificar permisos
function checkPermissions($required_role) {
    if (!isset($_SESSION['user']['tipo_usuario']) || 
        $_SESSION['user']['tipo_usuario'] !== $required_role) {
        // Limpiar cualquier salida pendiente
        ob_clean();
        
        // Redirigir a la página de error o dashboard según corresponda
        header('Location: error-403.html');
        exit();
    }
    return true;
}

// No cerrar el buffer de salida aquí, dejarlo para el final del script que lo incluye
// ob_end_flush();
?> 