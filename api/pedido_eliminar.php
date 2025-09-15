<?php
// API endpoint para eliminar permanentemente un pedido
// api/pedido_eliminar.php

// Incluir archivos necesarios
require_once '../database.php';
require_once '../auth_functions.php';

// Verificar que el usuario esté autenticado y sea administrador
require_admin(); // Esta función verificará si el usuario es admin, si no lo es, redirigirá

// Establecer cabeceras para JSON
header('Content-Type: application/json');

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido. Use POST o DELETE para esta operación.'
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
    // Iniciar transacción para garantizar integridad de datos
    $conexion->begin_transaction();
    
    // Verificar que el pedido existe
    $stmt_check = $conexion->prepare("SELECT id FROM pedidos WHERE id = ?");
    $stmt_check->bind_param("i", $pedido_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Pedido no encontrado");
    }
    
    // Primero eliminar los detalles del pedido (registros hijos)
    $stmt_delete_items = $conexion->prepare("DELETE FROM detalle_pedido WHERE id_pedido = ?");
    $stmt_delete_items->bind_param("i", $pedido_id);
    
    if (!$stmt_delete_items->execute()) {
        throw new Exception("Error al eliminar detalles del pedido: " . $stmt_delete_items->error);
    }
    
    // Luego eliminar el pedido principal
    $stmt_delete = $conexion->prepare("DELETE FROM pedidos WHERE id = ?");
    $stmt_delete->bind_param("i", $pedido_id);
    
    if (!$stmt_delete->execute()) {
        throw new Exception("Error al eliminar el pedido: " . $stmt_delete->error);
    }
    
    // Registrar la acción (opcional, si tienes una tabla de log)
    $usuario_id = $_SESSION['usuario_id'];
    $accion = "Eliminación de pedido";
    $detalles = "Pedido #$pedido_id eliminado permanentemente por el usuario #$usuario_id";
    
    // Verificar si la tabla existe antes de insertar
    $result_table = $conexion->query("SHOW TABLES LIKE 'log_acciones'");
    if ($result_table->num_rows > 0) {
        $stmt_log = $conexion->prepare("INSERT INTO log_acciones (usuario_id, accion, detalles, fecha) VALUES (?, ?, ?, NOW())");
        $stmt_log->bind_param("iss", $usuario_id, $accion, $detalles);
        $stmt_log->execute();
    }
    
    // Confirmar transacción
    $conexion->commit();
    
    // Devolver respuesta exitosa
    echo json_encode([
        'status' => 'success',
        'message' => 'Pedido eliminado permanentemente',
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
?>