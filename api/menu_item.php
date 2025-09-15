<?php
// API endpoint para obtener un ítem específico del menú
// api/menu_item.php

// Incluir archivos necesarios
require_once '../database.php';
require_once '../auth_functions.php';

// Verificar que el usuario esté autenticado
require_login();

// Establecer cabeceras para JSON
header('Content-Type: application/json');

// Verificar que se proporciona un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Se requiere el ID del ítem'
    ]);
    exit;
}

$item_id = intval($_GET['id']);

try {
    // Consultar el ítem por ID
    $query = "SELECT id, nombre, descripcion, precio, imagen, categoria, disponible 
              FROM menu_items WHERE id = ?";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ítem no encontrado'
        ]);
        exit;
    }
    
    $item = $result->fetch_assoc();
    
    // Enviar respuesta
    echo json_encode([
        'status' => 'success',
        'item' => $item
    ]);
    
} catch (Exception $e) {
    // En caso de error, devolver mensaje de error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener ítem del menú: ' . $e->getMessage()
    ]);
}