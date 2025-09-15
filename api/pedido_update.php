<?php
// api/pedido_update.php - Versión con logging completo

// Primero, asegurarnos que no haya salida de HTML por errores PHP
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Crear un archivo de registro
$log_file = '../debug_pedido_update.log';
file_put_contents($log_file, "--- Nueva solicitud: " . date('Y-m-d H:i:s') . " ---\n", FILE_APPEND);
file_put_contents($log_file, "GET: " . print_r($_GET, true) . "\n", FILE_APPEND);
file_put_contents($log_file, "POST: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents($log_file, "Input: " . file_get_contents('php://input') . "\n", FILE_APPEND);

// Establecer cabeceras para JSON
header('Content-Type: application/json');

try {
    // Incluir archivos necesarios
    require_once '../database.php';
    require_once '../auth_functions.php';
    
    file_put_contents($log_file, "Archivos incluidos correctamente\n", FILE_APPEND);
    
    // Verificar que el usuario esté autenticado
    if (!is_logged_in()) {
        file_put_contents($log_file, "Usuario no autenticado\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
        exit;
    }
    
    file_put_contents($log_file, "Usuario autenticado: " . $_SESSION['usuario_rol'] . "\n", FILE_APPEND);
    
    // Obtener el ID del pedido
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        file_put_contents($log_file, "ID de pedido no proporcionado\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'ID de pedido no proporcionado']);
        exit;
    }
    
    $pedido_id = intval($_GET['id']);
    file_put_contents($log_file, "ID de pedido: $pedido_id\n", FILE_APPEND);
    
    // Obtener el nuevo estado
    $input = json_decode(file_get_contents('php://input'), true);
    file_put_contents($log_file, "Input decodificado: " . print_r($input, true) . "\n", FILE_APPEND);
    
    if (!$input || !isset($input['estado'])) {
        file_put_contents($log_file, "No se proporcionó un nuevo estado\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'No se proporcionó un nuevo estado']);
        exit;
    }
    
    $nuevo_estado = $input['estado'];
    file_put_contents($log_file, "Nuevo estado: $nuevo_estado\n", FILE_APPEND);
    
    // Obtener el estado actual
    $stmt_actual = $conexion->prepare("SELECT estado FROM pedidos WHERE id = ?");
    $stmt_actual->bind_param("i", $pedido_id);
    $stmt_actual->execute();
    $result_actual = $stmt_actual->get_result();
    
    if ($result_actual->num_rows === 0) {
        file_put_contents($log_file, "Pedido no encontrado\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'Pedido no encontrado']);
        exit;
    }
    
    $row_actual = $result_actual->fetch_assoc();
    $estado_actual = $row_actual['estado'];
    file_put_contents($log_file, "Estado actual: $estado_actual\n", FILE_APPEND);
    
    // Actualizar el estado directamente
    $stmt_update = $conexion->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $stmt_update->bind_param("si", $nuevo_estado, $pedido_id);
    
    $resultado = $stmt_update->execute();
    file_put_contents($log_file, "Resultado de actualización: " . ($resultado ? "éxito" : "error: " . $stmt_update->error) . "\n", FILE_APPEND);
    
    if ($resultado) {
        $respuesta = [
            'status' => 'success',
            'message' => "Estado actualizado correctamente",
            'pedido_id' => $pedido_id,
            'nuevo_estado' => $nuevo_estado,
            'estado_anterior' => $estado_actual
        ];
        file_put_contents($log_file, "Respuesta exitosa: " . json_encode($respuesta) . "\n", FILE_APPEND);
        echo json_encode($respuesta);
    } else {
        throw new Exception("Error al actualizar el estado: " . $stmt_update->error);
    }
    
} catch (Exception $e) {
    file_put_contents($log_file, "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($log_file, "Stack trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

file_put_contents($log_file, "--- Fin de la solicitud ---\n\n", FILE_APPEND);
?>