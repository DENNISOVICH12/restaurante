<?php
// API endpoint para obtener ítems del menú
// api/menu.php

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivos necesarios
require_once '../database.php';
require_once '../auth_functions.php';

// Verificar que el usuario esté autenticado
require_login();

// Establecer cabeceras para JSON
header('Content-Type: application/json');

try {
    // Verificar si se solicitó una categoría específica
    $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
    
    // Construir consulta base
    $query = "SELECT id, nombre, descripcion, precio, imagen, categoria, disponible 
              FROM menu_items";
    
    $params = [];
    $types = "";
    
    // Filtrar por categoría si se especificó
    if ($categoria) {
        $query .= " WHERE categoria = ?";
        $params[] = $categoria;
        $types .= "s";
    }
    
    $query .= " ORDER BY nombre ASC";
    
    // Preparar y ejecutar consulta
    $stmt = $conexion->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'descripcion' => $row['descripcion'],
            'precio' => $row['precio'],
            'imagen' => $row['imagen'],
            'categoria' => $row['categoria'],
            'disponible' => $row['disponible']
        ];
    }
    
    // Para depuración, agregar información adicional
    $response = [
        'status' => 'success',
        'items' => $items,
        'count' => count($items),
        'categoria' => $categoria
    ];
    
    // Enviar respuesta
    echo json_encode($response);
    
} catch (Exception $e) {
    // En caso de error, devolver mensaje de error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener ítems del menú: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}