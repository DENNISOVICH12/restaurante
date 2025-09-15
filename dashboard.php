<?php
// Mostrar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivos necesarios
require_once 'database.php';
require_once 'auth_functions.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración | Restaurante</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- Barra lateral -->
        <div class="sidebar">
            <div class="logo">
                <h2>RESTAURANTE</h2>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="user-details">
                    <p class="user-name"><?php echo $_SESSION['usuario_nombre']; ?></p>
                    <p class="user-role"><?php echo ucfirst($_SESSION['usuario_rol']); ?></p>
                </div>
            </div>
            <nav class="menu">
                <ul>
                    <li class="active" data-page="dashboard-home">
                        <i class="fa-solid fa-home"></i>
                        <span>Inicio</span>
                    </li>
                    <li data-page="pedidos">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Pedidos</span>
                    </li>
                    <li data-page="menu-items">
                        <i class="fa-solid fa-utensils"></i>
                        <span>Menú</span>
                    </li>
                    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                    <li data-page="usuarios">
                        <i class="fa-solid fa-users"></i>
                        <span>Usuarios</span>
                    </li>
                    <li data-page="estadisticas">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Estadísticas</span>
                    </li>
                    <?php endif; ?>
                    <li data-page="configuracion">
                        <i class="fa-solid fa-gear"></i>
                        <span>Configuración</span>
                    </li>
                    <li>
                        <a href="index.php" target="_blank" style="display: flex; align-items: center; color: inherit;">
                            <i class="fa-solid fa-globe" style="margin-right: 10px; font-size: 18px; width: 20px; text-align: center;"></i>
                            <span>Ver Sitio Web</span>
                        </a>
                    </li>
                    <li id="logout">
                        <i class="fa-solid fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Contenido principal -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Panel de Control</h1>
                </div>
                <div class="actions">
                    <div class="search-box">
                        <input type="text" placeholder="Buscar...">
                        <i class="fa-solid fa-search"></i>
                    </div>
                    <div class="notifications">
                        <i class="fa-solid fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                </div>
            </div>

            <!-- Páginas del dashboard -->
            <div class="pages">
                <!-- Página de inicio -->
                <div class="page active" id="dashboard-home">
                    <div class="widgets">
                        <div class="widget">
                            <div class="widget-icon" style="background-color: #FFD700;">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div class="widget-data">
                                <h3>Pedidos Pendientes</h3>
                                <p class="number" id="pedidos-pendientes">0</p>
                            </div>
                        </div>
                        <div class="widget">
                            <div class="widget-icon" style="background-color: #4CAF50;">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                            <div class="widget-data">
                                <h3>Ventas del día</h3>
                                <p class="number" id="ventas-dia">$0</p>
                            </div>
                        </div>
                        <div class="widget">
                            <div class="widget-icon" style="background-color: #2196F3;">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="widget-data">
                                <h3>Clientes nuevos</h3>
                                <p class="number" id="clientes-nuevos">0</p>
                            </div>
                        </div>
                        <div class="widget">
                            <div class="widget-icon" style="background-color: #9C27B0;">
                                <i class="fa-solid fa-utensils"></i>
                            </div>
                            <div class="widget-data">
                                <h3>Platos más vendidos</h3>
                                <p class="text" id="plato-popular">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="recent-section">
                        <h2>Pedidos Recientes</h2>
                        <div class="table-container" id="recent-orders-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-orders">
                                    <!-- Los pedidos recientes se cargarán mediante AJAX -->
                                    <tr class="loading-placeholder">
                                        <td colspan="6">Cargando pedidos recientes...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Página de pedidos -->
                <div class="page" id="pedidos">
                    <h2>Gestión de Pedidos</h2>
                    <div class="filters">
                        <div class="filter-group">
                            <label for="estado-pedido">Estado:</label>
                            <select id="estado-pedido">
                                <option value="todos">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="en_preparacion">En preparación</option>
                                <option value="listo">Listo</option>
                                <option value="en_entrega">En entrega</option>
                                <option value="entregado">Entregado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="fecha-desde">Desde:</label>
                            <input type="date" id="fecha-desde">
                        </div>
                        <div class="filter-group">
                            <label for="fecha-hasta">Hasta:</label>
                            <input type="date" id="fecha-hasta">
                        </div>
                        <button class="btn btn-primary" id="filtrar-pedidos">
                            <i class="fa-solid fa-filter"></i> Filtrar
                        </button>
                    </div>
                    
                    <div class="table-container">
                        <table id="tabla-pedidos">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="lista-pedidos">
                                <!-- Los pedidos se cargarán mediante AJAX -->
                                <tr class="loading-placeholder">
                                    <td colspan="7">Cargando pedidos...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="pagination" id="paginacion-pedidos">
                        <!-- Paginación se generará mediante JavaScript -->
                    </div>
                </div>

                <!-- Página de gestión del menú -->
                <div class="page" id="menu-items">
                    <div class="page-header">
                        <h2>Gestión del Menú</h2>
                        <button class="btn btn-success" id="nuevo-item-menu">
                            <i class="fa-solid fa-plus"></i> Nuevo Ítem
                        </button>
                    </div>
                    
                    <div class="tabs">
                        <div class="tab active" data-tab="platos">Platos</div>
                        <div class="tab" data-tab="bebidas">Bebidas</div>
                        <div class="tab" data-tab="postres">Postres</div>
                    </div>
                    
                    <div class="tab-content active" id="tab-platos">
                        <!-- Contenido de platos se cargará mediante JavaScript -->
                    </div>
                    
                    <div class="tab-content" id="tab-bebidas">
                        <!-- Contenido de bebidas se cargará mediante JavaScript -->
                    </div>
                    
                    <div class="tab-content" id="tab-postres">
                        <!-- Contenido de postres se cargará mediante JavaScript -->
                    </div>
                </div>

                <!-- Página de gestión de usuarios (solo admin) -->
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                <div class="page" id="usuarios">
                    <div class="page-header">
                        <h2>Gestión de Usuarios</h2>
                        <button class="btn btn-success" id="nuevo-usuario">
                            <i class="fa-solid fa-user-plus"></i> Nuevo Usuario
                        </button>
                    </div>
                    
                    <div class="table-container">
                        <table id="tabla-usuarios">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Fecha Registro</th>
                                    <th>Último Acceso</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="lista-usuarios">
                                <!-- Los usuarios se cargarán mediante AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Página de estadísticas (solo admin) -->
                <div class="page" id="estadisticas">
                    <h2>Estadísticas y Reportes</h2>
                    
                    <div class="card-container">
                        <div class="card">
                            <h3>Ventas por Período</h3>
                            <div class="chart-container">
                                <canvas id="ventas-chart"></canvas>
                            </div>
                        </div>
                        
                        <div class="card">
                            <h3>Productos Más Vendidos</h3>
                            <div class="chart-container">
                                <canvas id="productos-chart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-container">
                        <div class="card">
                            <h3>Clientes Frecuentes</h3>
                            <div class="chart-container">
                                <canvas id="clientes-chart"></canvas>
                            </div>
                        </div>
                        
                        <div class="card">
                            <h3>Horarios de Mayor Demanda</h3>
                            <div class="chart-container">
                                <canvas id="horarios-chart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="export-options">
                        <h3>Exportar Reportes</h3>
                        <div class="btn-group">
                            <button class="btn btn-secondary" id="export-pdf">
                                <i class="fa-solid fa-file-pdf"></i> PDF
                            </button>
                            <button class="btn btn-secondary" id="export-excel">
                                <i class="fa-solid fa-file-excel"></i> Excel
                            </button>
                            <button class="btn btn-secondary" id="export-csv">
                                <i class="fa-solid fa-file-csv"></i> CSV
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Página de configuración -->
                <div class="page" id="configuracion">
                    <h2>Configuración</h2>
                    
                    <div class="config-section">
                        <h3>Perfil de Usuario</h3>
                        <form id="form-perfil" class="form-grid">
                            <div class="form-group">
                                <label for="nombre-perfil">Nombre:</label>
                                <input type="text" id="nombre-perfil" name="nombre" value="<?php echo $_SESSION['usuario_nombre']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="correo-perfil">Correo:</label>
                                <input type="email" id="correo-perfil" name="correo" value="<?php echo $_SESSION['usuario_correo']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="password-actual">Contraseña Actual:</label>
                                <input type="password" id="password-actual" name="password_actual">
                            </div>
                            <div class="form-group">
                                <label for="password-nueva">Nueva Contraseña:</label>
                                <input type="password" id="password-nueva" name="password_nueva">
                            </div>
                            <div class="form-group">
                                <label for="password-confirmar">Confirmar Contraseña:</label>
                                <input type="password" id="password-confirmar" name="password_confirmar">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                    
                    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                    <div class="config-section">
                        <h3>Configuración del Restaurante</h3>
                        <form id="form-restaurante" class="form-grid">
                            <div class="form-group">
                                <label for="nombre-restaurante">Nombre del Restaurante:</label>
                                <input type="text" id="nombre-restaurante" name="nombre_restaurante">
                            </div>
                            <div class="form-group">
                                <label for="direccion-restaurante">Dirección:</label>
                                <input type="text" id="direccion-restaurante" name="direccion">
                            </div>
                            <div class="form-group">
                                <label for="telefono-restaurante">Teléfono:</label>
                                <input type="text" id="telefono-restaurante" name="telefono">
                            </div>
                            <div class="form-group">
                                <label for="email-restaurante">Email:</label>
                                <input type="email" id="email-restaurante" name="email">
                            </div>
                            <div class="form-group">
                                <label for="horario-restaurante">Horario:</label>
                                <input type="text" id="horario-restaurante" name="horario">
                            </div>
                            <div class="form-group wide">
                                <label for="logo-restaurante">Logo:</label>
                                <input type="file" id="logo-restaurante" name="logo">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales para diferentes operaciones -->
    <!-- Modal para detalles de pedido -->
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
                    <select id="cambiar-estado">
                        <option value="pendiente">Pendiente</option>
                        <option value="en_preparacion">En preparación</option>
                        <option value="listo">Listo</option>
                        <option value="en_entrega">En entrega</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
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
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="items-pedido">
                        <!-- Detalles de productos se cargarán aquí -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Total:</strong></td>
                            <td id="total-pedido" class="text-right"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="btn-group">
                <button class="btn btn-secondary" id="imprimir-pedido">
                    <i class="fa-solid fa-print"></i> Imprimir
                </button>
                <button class="btn btn-danger" id="cancelar-pedido">
                    <i class="fa-solid fa-ban"></i> Cancelar Pedido
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para crear/editar ítem del menú -->
    <div class="modal" id="modal-item-menu">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="titulo-modal-item">Nuevo Ítem del Menú</h2>
            <form id="form-item-menu" enctype="multipart/form-data">
                <input type="hidden" id="item-id" name="id">
                <div class="form-group">
                    <label for="item-nombre">Nombre:</label>
                    <input type="text" id="item-nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="item-categoria">Categoría:</label>
                    <select id="item-categoria" name="categoria" required>
                        <option value="plato">Plato</option>
                        <option value="bebida">Bebida</option>
                        <option value="postre">Postre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="item-descripcion">Descripción:</label>
                    <textarea id="item-descripcion" name="descripcion" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="item-precio">Precio (COP):</label>
                    <input type="number" id="item-precio" name="precio" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="item-imagen">Imagen:</label>
                    <input type="file" id="item-imagen" name="imagen" accept="image/*">
                    <div id="preview-imagen" class="image-preview"></div>
                </div>
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="item-disponible" name="disponible" checked>
                        Disponible
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secondary" id="cancelar-item">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para crear/editar usuario (solo admin) -->
    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
    <div class="modal" id="modal-usuario">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="titulo-modal-usuario">Nuevo Usuario</h2>
            <form id="form-usuario">
                <input type="hidden" id="usuario-id" name="id">
                <div class="form-group">
                    <label for="usuario-nombre">Nombre:</label>
                    <input type="text" id="usuario-nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="usuario-correo">Correo:</label>
                    <input type="email" id="usuario-correo" name="correo" required>
                </div>
                <div class="form-group password-field">
                    <label for="usuario-password">Contraseña:</label>
                    <input type="password" id="usuario-password" name="password">
                    <small class="help-text">Dejar en blanco para mantener la contraseña actual (al editar)</small>
                </div>
                <div class="form-group">
                    <label for="usuario-rol">Rol:</label>
                    <select id="usuario-rol" name="rol" required>
                        <option value="empleado">Empleado</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="usuario-activo" name="activo" checked>
                        Activo
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secondary" id="cancelar-usuario">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal de confirmación -->
    <div class="modal" id="modal-confirmacion">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirmar Acción</h2>
            <p id="mensaje-confirmacion">¿Estás seguro de que deseas realizar esta acción?</p>
            <div class="btn-group">
                <button class="btn btn-danger" id="confirmar-si">Sí, continuar</button>
                <button class="btn btn-secondary" id="confirmar-no">No, cancelar</button>
            </div>
        </div>
    </div>

    <!-- Script de Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Script principal del dashboard -->
    <script src="js/dashboard.js"></script>
    
    <!-- Script para visualización del menú -->
    <script src="js/menu-display.js"></script>

    <script src="js/dashboard-realtime.js"></script>

    <script src="js/pedidos.js"></script>
</body>
</html>