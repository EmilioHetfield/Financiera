<?php
function getUserProfile($conn, $user_id) {
    try {
        $sql_user_info = "SELECT 
            id,
            nombre as full_name,
            usuario as email,
            tipo_usuario as id_type,
            telefono as phone,
            COALESCE(profile_image, 'assets/img/profile-img.jpg') as profile_image
            FROM usuarios 
            WHERE id = :user_id";
        
        $stmt = $conn->prepare($sql_user_info);
        $stmt->execute(['user_id' => $user_id]);
        $result_user_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result_user_info) {
            return $result_user_info;
        } else {
            return [
                'full_name' => $_SESSION['user']['nombre'],
                'email' => $_SESSION['user']['usuario'],
                'id_type' => $_SESSION['user']['tipo_usuario'],
                'phone' => '',
                'profile_image' => 'assets/img/profile-img.jpg'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error en getUserProfile: " . $e->getMessage());
        return null;
    }
}

function getTipoUsuario($tipo) {
    switch ($tipo) {
        case 'master':
            return "Admin";
        case 'vendedor':
            return "Vendedor";
        case 'autorizador':
            return "Autorizador";
        default:
            return "Desconocido";
    }
}

function updateProfileImage($conn, $user_id, $file) {
    try {
        // Verificar si se subió un archivo
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo');
        }

        // Validar el tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF');
        }

        // Crear directorio si no existe
        $upload_dir = '../assets/img/profile/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generar nombre único para el archivo
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
        $file_path = 'assets/img/profile/' . $new_filename; // Ruta relativa para la BD
        $full_path = $upload_dir . $new_filename; // Ruta completa para guardar el archivo

        // Mover el archivo
        if (!move_uploaded_file($file['tmp_name'], $full_path)) {
            throw new Exception('Error al guardar el archivo');
        }

        // Obtener imagen anterior
        $sql_old_image = "SELECT profile_image FROM usuarios WHERE id = :user_id";
        $stmt_old = $conn->prepare($sql_old_image);
        $stmt_old->execute(['user_id' => $user_id]);
        $old_image = $stmt_old->fetchColumn();

        // Actualizar la ruta en la base de datos
        $sql = "UPDATE usuarios SET profile_image = :image_path WHERE id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'image_path' => $file_path,
            'user_id' => $user_id
        ]);

        // Eliminar imagen anterior si existe y no es la imagen por defecto
        if ($old_image && 
            $old_image !== 'assets/img/profile-img.jpg' && 
            file_exists('../' . $old_image)) {
            unlink('../' . $old_image);
        }

        return [
            'success' => true,
            'message' => 'Imagen actualizada correctamente',
            'path' => $file_path
        ];

    } catch (Exception $e) {
        error_log("Error en updateProfileImage: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Función para validar el tamaño y tipo de imagen
function validateProfileImage($file) {
    $max_size = 5 * 1024 * 1024; // 5MB
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $errors = [];

    if ($file['size'] > $max_size) {
        $errors[] = 'El archivo es demasiado grande. El tamaño máximo permitido es 5MB.';
    }

    $file_type = mime_content_type($file['tmp_name']);
    if (!in_array($file_type, $allowed_types)) {
        $errors[] = 'Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
?>
