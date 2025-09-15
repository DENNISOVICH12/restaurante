<?php
// API endpoint para obtener pedidos recientes
// api/pedidos_recientes.php

// Incluir archivos necesarios
require_once '../database.php';
require_once '../auth_functions.php';

// Verificar que el usuario esté autenticado
require_login();

// Establecer cabeceras para JSON
header('Content-Type: application/json');

try {
    // Obtener los 10 pedidos más recientes
    $query = "SELECT p.id, c.nombre_cliente as cliente, p.fecha, 
             (SELECT SUM(dp.precio * dp.cantidad) FROM detalle_pedido dp WHERE dp.id_pedido = p.id) as total,
             p.estado
             FROM pedidos p
             JOIN clientes c ON p.id_cliente = c.id
             ORDER BY p.fecha DESC
             LIMIT 10";
    
    $result = $conexion->query($query);
    
    $pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = [
            'id' => $row['id'],
            'cliente' => $row['cliente'],
            'fecha' => $row['fecha'],
            'total' => $row['total'],
            'estado' => $row['estado']
        ];
    }
    
    // Enviar respuesta
    echo json_encode($pedidos);
    
} catch (Exception $e) {
    // En caso de error, devolver mensaje de error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener pedidos recientes: ' . $e->getMessage()
    ]);
}