<?php
// api/pedidos_cocina.php
// API endpoint para obtener pedidos para la vista de cocina

// Incluir archivos necesarios
require_once '../database.php';
require_once '../auth_functions.php';

// Verificar que el usuario esté autenticado
require_login();

// Verificar que sea cocinero o admin
if (!is_cocinero() && !is_admin()) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'No tiene permisos para acceder a esta información'
    ]);
    exit;
}

// Establecer cabeceras para JSON
header('Content-Type: application/json');

try {
    // Obtener el estado a filtrar
    $estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';
    
    // Construir consulta base
    $query = "SELECT p.id, p.fecha, p.estado, c.nombre_cliente as cliente
              FROM pedidos p
              JOIN clientes c ON p.id_cliente = c.id
              WHERE p.estado != 'cancelado' AND p.estado != 'entregado' AND p.estado != 'en_entrega'";
    
    // Aplicar filtro por estado si no es 'todos'
    if ($estado !== 'todos') {
        $query .= " AND p.estado = ?";
    }
    
    $query .= " ORDER BY p.fecha DESC";
    
    // Preparar y ejecutar consulta
    $stmt = $conexion->prepare($query);
    
    if ($estado !== 'todos') {
        $stmt->bind_param("s", $estado);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pedidos = [];
    
    while ($row = $result->fetch_assoc()) {
        // Obtener items del pedido
        $query_items = "SELECT dp.nombre_producto, dp.cantidad, dp.descripcion
                        FROM detalle_pedido dp
                        WHERE dp.id_pedido = ?";
        
        $stmt_items = $conexion->prepare($query_items);
        $stmt_items->bind_param("i", $row['id']);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        
        $items = [];
        while ($item = $result_items->fetch_assoc()) {
            $items[] = [
                'nombre' => $item['nombre_producto'],
                'cantidad' => $item['cantidad'],
                'descripcion' => $item['descripcion']
            ];
        }
        
        // Construir respuesta completa
        $pedidos[] = [
            'id' => $row['id'],
            'fecha' => $row['fecha'],
            'estado' => $row['estado'],
            'cliente' => $row['cliente'],
            'items' => $items
        ];
    }
    
    // Enviar respuesta
    echo json_encode($pedidos);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener pedidos: ' . $e->getMessage()
    ]);
}