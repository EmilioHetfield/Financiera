<?php
session_start();
require_once '../config.php';
require_once 'profile_functions.php';

if (!isset($_SESSION['user']) || !isset($_FILES['profile_image'])) {
    header('Location: ../users-profile.php');
    exit();
}

try {
    $user_id = $_SESSION['user']['id'];
    
    // Validar la imagen
    $validation = validateProfileImage($_FILES['profile_image']);
    if (!$validation['valid']) {
        $_SESSION['error'] = implode(' ', $validation['errors']);
        header('Location: ../users-profile.php');
        exit();
    }

    // Actualizar la imagen
    $result = updateProfileImage($conn, $user_id, $_FILES['profile_image']);

    if ($result['success']) {
        // Actualizar la sesión con la nueva ruta de la imagen
        $_SESSION['user']['profile_image'] = $result['path'];
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }

} catch (Exception $e) {
    error_log("Error al actualizar imagen de perfil: " . $e->getMessage());
    $_SESSION['error'] = "Error al actualizar la imagen de perfil";
}

header('Location: ../users-profile.php');
exit();
?> 