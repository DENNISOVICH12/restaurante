<?php
// API endpoint para crear un nuevo ítem del menú
// api/menu_create.php

// Incluir archivos necesarios
require_once '../database.php';
require_once '../auth_functions.php';

// Verificar que el usuario esté autenticado
require_login();

// Establecer cabeceras para JSON
header('Content-Type: application/json');

try {
    // Verificar que se recibieron los datos necesarios
    if (!isset($_POST['nombre'], $_POST['categoria'], $_POST['precio'])) {
        throw new Exception("Faltan datos obligatorios");
    }

    // Obtener datos del formulario
    $nombre = trim($_POST['nombre']);
    $categoria = $_POST['categoria'];
    $precio = floatval($_POST['precio']);
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    // Validar datos
    if (empty($nombre)) {
        throw new Exception("El nombre es obligatorio");
    }

    if (!in_array($categoria, ['plato', 'bebida', 'postre'])) {
        throw new Exception("Categoría no válida");
    }

    if ($precio <= 0) {
        throw new Exception("El precio debe ser mayor que cero");
    }

    // Manejar la imagen
    $imagen = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
        $file_type = $_FILES['imagen']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("Tipo de archivo no permitido. Solo se permiten imágenes JPEG, PNG, GIF y WEBP.");
        }
        
        // Determinar la carpeta de destino según la categoría
        $target_dir = ($categoria == 'bebida') ? '../img-bebidas/' : '../img/';
        
        // Crear un nombre único para el archivo
        $file_extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $unique_id = uniqid();
        $imagen = $unique_id . '.' . $file_extension;
        $target_file = $target_dir . $imagen;
        
        // Intentar mover el archivo
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
            throw new Exception("Error al subir la imagen");
        }
    } else {
        // Si no se subió imagen, usar una imagen por defecto
        $imagen = ($categoria == 'bebida') ? 'default_bebida.jpg' : 'default_plato.jpg';
    }

    // Insertar en la base de datos
    $query = "INSERT INTO menu_items (nombre, categoria, descripcion, precio, imagen, disponible) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("sssdsi", $nombre, $categoria, $descripcion, $precio, $imagen, $disponible);
    
    if ($stmt->execute()) {
        $item_id = $stmt->insert_id;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Ítem creado correctamente',
            'item_id' => $item_id
        ]);
    } else {
        throw new Exception("Error al crear el ítem: " . $stmt->error);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}