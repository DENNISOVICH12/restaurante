<?php
// API endpoint para cancelar un pedido
// api/pedido_cancelar.php

// Incluir archivos necesarios
require_once '../database.php';
require_once '../auth_functions.php';

// Verificar que el usuario esté autenticado
require_login();

// Establecer cabeceras para JSON
header('Content-Type: application/json');

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido. Use POST para esta operación.'
    ]);
    exit;
}

// Verificar que se proporcionó un ID de pedido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'ID de pedido no proporcionado'
    ]);
    exit;
}

$pedido_id = intval($_GET['id']);

try {
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // Verificar que el pedido existe
    $stmt_check = $conexion->prepare("SELECT id, estado FROM pedidos WHERE id = ?");
    $stmt_check->bind_param("i", $pedido_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Pedido no encontrado");
    }
    
    $pedido = $result->fetch_assoc();
    
    // Verificar si el pedido ya está cancelado
    if ($pedido['estado'] === 'cancelado') {
        throw new Exception("Este pedido ya está cancelado");
    }
    
    // Actualizar estado del pedido a 'cancelado'
    $stmt_update = $conexion->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id = ?");
    $stmt_update->bind_param("i", $pedido_id);
    
    if (!$stmt_update->execute()) {
        throw new Exception("Error al cancelar el pedido: " . $stmt_update->error);
    }
    
    // Registrar la acción (opcional, si tienes una tabla de log)
    $usuario_id = $_SESSION['usuario_id'];
    $accion = "Cancelación de pedido";
    $detalles = "Pedido #$pedido_id cancelado por el usuario #$usuario_id";
    
    $stmt_log = $conexion->prepare("INSERT INTO log_acciones (usuario_id, accion, detalles, fecha) VALUES (?, ?, ?, NOW())");
    
    // Verificar si la tabla existe antes de insertar
    $result_table = $conexion->query("SHOW TABLES LIKE 'log_acciones'");
    if ($result_table->num_rows > 0 && $stmt_log) {
        $stmt_log->bind_param("iss", $usuario_id, $accion, $detalles);
        $stmt_log->execute();
    }
    
    // Confirmar transacción
    $conexion->commit();
    
    // Devolver respuesta exitosa
    echo json_encode([
        'status' => 'success',
        'message' => 'Pedido cancelado correctamente',
        'pedido_id' => $pedido_id
    ]);
    
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conexion->rollback();
    
    // Devolver mensaje de error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}