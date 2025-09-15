<?php
// API endpoint para obtener datos del dashboard
// api/dashboard_data.php

// Incluir archivos necesarios
require_once '../database.php';
require_once '../auth_functions.php';

// Verificar que el usuario esté autenticado
require_login();

// Establecer cabeceras para JSON
header('Content-Type: application/json');

try {
    // Obtener fecha actual
    $hoy = date('Y-m-d');
    
    // Para pruebas: también podemos ver datos de los últimos 30 días
    $periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'hoy';
    
    // 1. Pedidos pendientes (todos los pendientes, no solo de hoy)
    $query_pendientes = "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'";
    $result_pendientes = $conexion->query($query_pendientes);
    $pedidos_pendientes = $result_pendientes->fetch_assoc()['total'];
    
    // 2. Ventas
    if ($periodo == 'mes') {
        // Ventas del mes
        $query_ventas = "SELECT COALESCE(SUM(dp.precio * dp.cantidad), 0) as total 
                        FROM pedidos p 
                        JOIN detalle_pedido dp ON p.id = dp.id_pedido 
                        WHERE DATE(p.fecha) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        AND p.estado != 'cancelado'";
        $result_ventas = $conexion->query($query_ventas);
        $titulo_ventas = "Ventas del mes";
    } else {
        // Ventas del día
        $query_ventas = "SELECT COALESCE(SUM(dp.precio * dp.cantidad), 0) as total 
                        FROM pedidos p 
                        JOIN detalle_pedido dp ON p.id = dp.id_pedido 
                        WHERE DATE(p.fecha) = ?
                        AND p.estado != 'cancelado'";
        $stmt_ventas = $conexion->prepare($query_ventas);
        $stmt_ventas->bind_param('s', $hoy);
        $stmt_ventas->execute();
        $result_ventas = $stmt_ventas->get_result();
        $titulo_ventas = "Ventas del día";
    }
    $ventas_total = $result_ventas->fetch_assoc()['total'];
    
    // 3. Clientes nuevos
    if ($periodo == 'mes') {
        // Clientes nuevos del mes
        $query_clientes = "SELECT COUNT(*) as total FROM clientes WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        $result_clientes = $conexion->query($query_clientes);
        $titulo_clientes = "Clientes nuevos (mes)";
    } else {
        // Clientes nuevos del día
        $query_clientes = "SELECT COUNT(*) as total FROM clientes WHERE DATE(fecha_registro) = ?";
        $stmt_clientes = $conexion->prepare($query_clientes);
        $stmt_clientes->bind_param('s', $hoy);
        $stmt_clientes->execute();
        $result_clientes = $stmt_clientes->get_result();
        $titulo_clientes = "Clientes nuevos (hoy)";
    }
    $clientes_nuevos = $result_clientes->fetch_assoc()['total'];
    
    // 4. Plato más vendido (últimos 30 días)
    $query_plato = "SELECT dp.nombre_producto as nombre, SUM(dp.cantidad) as total 
                    FROM detalle_pedido dp 
                    JOIN pedidos p ON dp.id_pedido = p.id 
                    WHERE p.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                    AND p.estado != 'cancelado' 
                    GROUP BY dp.nombre_producto 
                    ORDER BY total DESC 
                    LIMIT 1";
    $result_plato = $conexion->query($query_plato);
    
    if ($result_plato && $result_plato->num_rows > 0) {
        $plato = $result_plato->fetch_assoc();
        $plato_popular = $plato['nombre'];
    } else {
        $plato_popular = "Sin datos";
    }
    
    // 5. Pedidos recientes (últimos 10)
    $query_pedidos = "SELECT p.id, c.nombre_cliente AS cliente, p.fecha, p.estado,
                       (SELECT SUM(dp.precio * dp.cantidad) FROM detalle_pedido dp WHERE dp.id_pedido = p.id) AS total
                       FROM pedidos p
                       JOIN clientes c ON p.id_cliente = c.id
                       ORDER BY p.fecha DESC
                       LIMIT 10";
    
    $result_pedidos = $conexion->query($query_pedidos);
    $pedidos_recientes = [];
    
    if ($result_pedidos && $result_pedidos->num_rows > 0) {
        while ($row = $result_pedidos->fetch_assoc()) {
            $pedidos_recientes[] = [
                'id' => $row['id'],
                'cliente' => $row['cliente'],
                'fecha' => $row['fecha'],
                'total' => $row['total'],
                'estado' => $row['estado']
            ];
        }
    }
    
    // Enviar respuesta
    echo json_encode([
        'pedidos_pendientes' => $pedidos_pendientes,
        'ventas_dia' => $ventas_total,
        'titulo_ventas' => $titulo_ventas,
        'clientes_nuevos' => $clientes_nuevos,
        'titulo_clientes' => $titulo_clientes,
        'plato_popular' => $plato_popular,
        'pedidos_recientes' => $pedidos_recientes,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // En caso de error, devolver mensaje de error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener datos del dashboard: ' . $e->getMessage()
    ]);
}