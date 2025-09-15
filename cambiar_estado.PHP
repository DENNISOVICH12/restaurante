<?php
// cambiar_estado.php - Para cambiar estados con transiciones válidas
require_once 'database.php';
require_once 'auth_functions.php';

// Verificar que el usuario esté autenticado
require_login();

// Verificar que sea cocinero o admin
if (!is_cocinero() && !is_admin()) {
    header("Location: dashboard.php?error=no_permission");
    exit;
}

// Verificar parámetros
if (!isset($_GET['id']) || !isset($_GET['estado'])) {
    echo "<script>alert('Parámetros incorrectos'); window.location.href = 'cocina.php';</script>";
    exit;
}

$pedido_id = intval($_GET['id']);
$nuevo_estado = $_GET['estado'];

// Validar estado
$estados_validos = ['Pendiente', 'En_preparación', 'Listo'];
if (!in_array($nuevo_estado, $estados_validos)) {
    echo "<script>alert('Estado no válido'); window.location.href = 'cocina.php';</script>";
    exit;
}

// Obtener el estado actual
$stmt_check = $conexion->prepare("SELECT estado FROM pedidos WHERE id = ?");
$stmt_check->bind_param("i", $pedido_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Pedido no encontrado'); window.location.href = 'cocina.php';</script>";
    exit;
}

$pedido = $result->fetch_assoc();
$estado_actual = $pedido['estado'];

// Mapear estados antiguos si es necesario
$mapeo_estados = [
    'pendiente' => 'Pendiente',
    'en_preparacion' => 'En_preparación',
    'listo' => 'Listo'
];

if (isset($mapeo_estados[$estado_actual])) {
    $estado_actual = $mapeo_estados[$estado_actual];
}

// Verificar transiciones válidas
$transiciones_validas = [
    'pendiente' => ['en_preparacion'],
    'en_preparacion' => ['listo'],
    'listo' => [],
    'pendiente' => ['en_preparacion'],
    'en_preparacion' => ['listo']
];

// Si no es admin y la transición no es válida, mostrar error
if ($_SESSION['usuario_rol'] !== 'admin') {
    $estado_para_comprobar = isset($transiciones_validas[$estado_actual]) ? $estado_actual : 
                          (isset($mapeo_estados[$estado_actual]) ? $mapeo_estados[$estado_actual] : $estado_actual);
    
    if (!isset($transiciones_validas[$estado_para_comprobar]) || 
        !in_array($nuevo_estado, $transiciones_validas[$estado_para_comprobar])) {
        echo "<script>alert('No se puede cambiar de \"$estado_actual\" a \"$nuevo_estado\". Transiciones permitidas: " . 
            implode(", ", $transiciones_validas[$estado_para_comprobar] ?? []) . "'); window.location.href = 'cocina.php';</script>";
        exit;
    }
}

// Actualizar estado
$query = "UPDATE pedidos SET estado = ? WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("si", $nuevo_estado, $pedido_id);

if ($stmt->execute()) {
    echo "<script>alert('Estado actualizado correctamente de \"$estado_actual\" a \"$nuevo_estado\"'); window.location.href = 'cocina.php';</script>";
} else {
    echo "<script>alert('Error al actualizar estado: " . $stmt->error . "'); window.location.href = 'cocina.php';</script>";
}
?>