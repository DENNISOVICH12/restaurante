<?php
// api/pedidos_meseros.php - API para obtener pedidos para meseros
require_once '../database.php';
require_once '../auth_functions.php';

// Verificar que el usuario sea mesero o admin
require_mesero();

// Establecer cabeceras para JSON
header('Content-Type: application/json');

// Obtener filtro de estado si existe
$estado = isset($_GET['estado']) ? $_GET['estado'] : 'listo';

try {
    // Construir consulta según el estado solicitado
    $whereCond = "1=1"; // Condición default
    $params = [];
    $types = "";
    
    if ($estado !== 'todos') {
        $whereCond = "p.estado = ?";
        $params[] = $estado;
        $types .= "s";
    } else {
        $whereCond = "p.estado IN ('listo', 'en_entrega', 'entregado')";
    }

    // Consulta principal para obtener los pedidos
    $query = "
        SELECT p.id, p.fecha, p.estado, c.nombre_cliente as cliente,
        (SELECT SUM(dp.precio * dp.cantidad) FROM detalle_pedido dp WHERE dp.id_pedido = p.id) as total
        FROM pedidos p
        JOIN clientes c ON p.id_cliente = c.id
        WHERE $whereCond
        ORDER BY 
            CASE 
                WHEN p.estado = 'listo' THEN 1
                WHEN p.estado = 'en_entrega' THEN 2
                ELSE 3
            END,
            p.fecha DESC
    ";

    if (!empty($params)) {
        $stmt = $conexion->prepare($query);
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt = $conexion->prepare($query);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $pedidos = [];
    
    while ($row = $result->fetch_assoc()) {
        // Obtener los productos para cada pedido
        $query_items = "
            SELECT nombre_producto as nombre, categoria, precio, cantidad, descripcion
            FROM detalle_pedido
            WHERE id_pedido = ?
        ";
        
        $stmt_items = $conexion->prepare($query_items);
        $stmt_items->bind_param('i', $row['id']);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        
        $items = [];
        while ($item = $result_items->fetch_assoc()) {
            $items[] = $item;
        }
        
        $row['items'] = $items;
        $pedidos[] = $row;
    }
    
    // Depuración - quitar en producción
    error_log("Pedidos recuperados para meseros: " . count($pedidos));
    error_log("Estado filtrado: " . $estado);
    
    // Enviar respuesta
    echo json_encode($pedidos);
    
} catch (Exception $e) {
    // En caso de error, devolver mensaje de error
    error_log("Error en pedidos_meseros.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener pedidos: ' . $e->getMessage()
    ]);
}
?>