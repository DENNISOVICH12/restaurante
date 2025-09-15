<?php
// meseros.php - Vista para meseros
require_once 'database.php';
require_once 'auth_functions.php';

// Verificar que el usuario sea mesero o admin
require_mesero();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meseros - Restaurante</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/meseros.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="top-nav">
        <h1>Meseros - Pedidos Listos</h1>
        <div class="user-info">
            <span><?php echo $_SESSION['usuario_nombre']; ?> (<?php echo ucfirst($_SESSION['usuario_rol']); ?>)</span>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container" style="padding: 20px;">
        <div class="filter-bar">
            <h3>Filtrar pedidos:</h3>
            <button class="btn filtro-estado active" data-estado="listo">Listos para entregar</button>
            <button class="btn filtro-estado" data-estado="en_entrega">En entrega</button>
            <button class="btn filtro-estado" data-estado="entregado">Entregados</button>
        </div>
        
        <h2>Pedidos para entregar</h2>
        <div class="grid-container" id="pedidos-container">
            <!-- Los pedidos se cargarán dinámicamente aquí -->
            <div class="loading">Cargando pedidos...</div>
        </div>
    </div>

    <!-- Modal para detalles -->
    <div class="modal" id="modal-detalle-pedido">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Detalles del Pedido <span id="pedido-id"></span></h2>
            <div class="pedido-info">
                <div class="cliente-info">
                    <h3>Información del Cliente</h3>
                    <p><strong>Nombre:</strong> <span id="cliente-nombre"></span></p>
                    <p><strong>Teléfono:</strong> <span id="cliente-telefono"></span></p>
                    <p><strong>Dirección:</strong> <span id="cliente-direccion"></span></p>
                </div>
                <div class="pedido-status">
                    <h3>Estado del Pedido</h3>
                    <p id="estado-actual" style="font-weight: bold;"></p>
                </div>
            </div>
            <div class="detalle-items">
                <h3>Productos</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                            <th>Notas</th>
                        </tr>
                    </thead>
                    <tbody id="items-pedido">
                        <!-- Detalles de productos se cargarán aquí -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td id="total-pedido" colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="pedido-actions text-center" style="margin-top: 20px;">
                <button id="btn-marcar-entrega" class="btn btn-primary" style="display: none;">Marcar En Entrega</button>
                <button id="btn-marcar-entregado" class="btn btn-success" style="display: none;">Marcar como Entregado</button>
                <button id="btn-imprimir" class="btn btn-secondary">Imprimir</button>
            </div>
        </div>
    </div>
        <script src="js/mesero.js"></script>
</body>
</html>