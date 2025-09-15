<?php
// API endpoint para eliminar un ítem del menú
// api/menu_delete.php

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
    // Verificar primero si el ítem existe
    $check = $conexion->prepare("SELECT id FROM menu_items WHERE id = ?");
    $check->bind_param("i", $item_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ítem no encontrado'
        ]);
        exit;
    }
    
    // Eliminar el ítem
    $stmt = $conexion->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Ítem eliminado correctamente'
        ]);
    } else {
        throw new Exception($stmt->error);
    }
    
} catch (Exception $e) {
    // En caso de error, devolver mensaje de error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al eliminar ítem del menú: ' . $e->getMessage()
    ]);
}