<?php
// cocina.php - Vista para cocineros (archivo principal)
require_once 'database.php';
require_once 'auth_functions.php';

// Verificar que el usuario sea cocinero o admin
require_cocinero();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cocina - Restaurante</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/cocina.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="top-nav">
        <h1>Cocina - Panel de Pedidos</h1>
        <div class="user-info">
            <span><?php echo $_SESSION['usuario_nombre']; ?> (<?php echo ucfirst($_SESSION['usuario_rol']); ?>)</span>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container" style="padding: 20px;">
        <div class="filter-bar">
            <h3>Filtrar pedidos:</h3>
            <button class="filtro-estado active" data-estado="todos">Todos</button>
            <button class="filtro-estado" data-estado="Pendiente">Pendientes</button>
            <button class="filtro-estado" data-estado="En_preparación">En Preparación</button>
            <button class="filtro-estado" data-estado="Listo">Listos</button>
        </div>
        
        <h2>Pedidos Activos</h2>
        <div class="grid-container" id="pedidos-container">
            <!-- Los pedidos se cargarán dinámicamente aquí -->
            <div class="loading">Cargando pedidos...</div>
        </div>
    </div>

    <!-- Modal para detalles y acciones -->
    <div class="modal" id="modal-detalle-pedido">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Detalles del Pedido #<span id="pedido-id"></span></h2>
            <div class="pedido-info">
                <div class="cliente-info">
                    <h3>Información del Cliente</h3>
                    <p><strong>Nombre:</strong> <span id="cliente-nombre"></span></p>
                    <p><strong>Teléfono:</strong> <span id="cliente-telefono"></span></p>
                    <p><strong>Dirección:</strong> <span id="cliente-direccion"></span></p>
                </div>
                <div class="pedido-status">
                    <h3>Estado del Pedido</h3>
                    <select id="cambiar-estado">
                        <option value="pendiente">Pendiente</option>
                        <option value="en_preparacion">En preparación</option>
                        <option value="listo">Listo</option>
                    </select>
                    <button class="btn btn-primary" id="guardar-estado">Guardar Cambios</button>
                </div>
            </div>
            <div class="detalle-items">
                <h3>Productos</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Notas</th>
                        </tr>
                    </thead>
                    <tbody id="items-pedido">
                        <!-- Detalles de productos se cargarán aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="js/cocina.js"></script>
</body>
</html>