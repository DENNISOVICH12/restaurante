<?php
// API endpoint para obtener detalles de un pedido
// api/pedido_detalle.php

// Incluir archivos necesarios
require_once '../database.php';
require_once '../auth_functions.php';

// Verificar que el usuario esté autenticado
require_login();

// Establecer cabeceras para JSON
header('Content-Type: application/json');

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
    // Obtener información del pedido
    $query_pedido = "SELECT p.id, p.fecha, p.estado, c.id as cliente_id, c.nombre_cliente, c.telefono, c.direccion 
                     FROM pedidos p 
                     JOIN clientes c ON p.id_cliente = c.id 
                     WHERE p.id = ?";
    
    $stmt_pedido = $conexion->prepare($query_pedido);
    $stmt_pedido->bind_param('i', $pedido_id);
    $stmt_pedido->execute();
    $result_pedido = $stmt_pedido->get_result();
    
    if ($result_pedido->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Pedido no encontrado'
        ]);
        exit;
    }
    
    $pedido_info = $result_pedido->fetch_assoc();
    
    // Obtener items del pedido
    $query_items = "SELECT nombre_producto, categoria, precio, cantidad, descripcion 
                    FROM detalle_pedido 
                    WHERE id_pedido = ?";
    
    $stmt_items = $conexion->prepare($query_items);
    $stmt_items->bind_param('i', $pedido_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    
    $items = [];
    $total = 0;
    
    while ($row = $result_items->fetch_assoc()) {
        $subtotal = $row['precio'] * $row['cantidad'];
        $total += $subtotal;
        
        $items[] = [
            'nombre' => $row['nombre_producto'],
            'categoria' => $row['categoria'],
            'precio' => $row['precio'],
            'cantidad' => $row['cantidad'],
            'descripcion' => $row['descripcion'],
            'subtotal' => $subtotal
        ];
    }
    
    // Preparar respuesta completa
    $response = [
        'status' => 'success',
        'pedido' => [
            'id' => $pedido_info['id'],
            'fecha' => $pedido_info['fecha'],
            'estado' => $pedido_info['estado'],
            'total' => $total,
            'cliente' => [
                'id' => $pedido_info['cliente_id'],
                'nombre' => $pedido_info['nombre_cliente'],
                'telefono' => $pedido_info['telefono'],
                'direccion' => $pedido_info['direccion']
            ],
            'items' => $items
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // En caso de error, devolver mensaje de error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener detalles del pedido: ' . $e->getMessage()
    ]);
}