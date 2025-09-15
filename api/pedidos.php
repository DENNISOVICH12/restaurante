<?php
// pedidos.php
require_once 'auth_functions.php';
require_login(); // Asegurar que el usuario esté autenticado

// Incluir encabezado común
include_once 'header.php';
?>

<div class="container-fluid">
    <h1>Pedidos</h1>
    
    <div class="card">
        <div class="card-header">
            <h3>Gestión de Pedidos</h3>
        </div>
        <div class="card-body">
            <!-- Formulario de filtros -->
            <form id="filtros-form" class="mb-4">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="estado">Estado:</label>
                        <select id="estado" class="form-control">
                            <option value="todos">Todos</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En preparación">En preparación</option>
                            <option value="Listo">Listo</option>
                            <option value="Entregado">Entregado</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="fecha_desde">Desde:</label>
                        <input type="date" id="fecha_desde" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="fecha_hasta">Hasta:</label>
                        <input type="date" id="fecha_hasta" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="button" id="filtrar-btn" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <button type="reset" class="btn btn-secondary ml-2">
                            Limpiar
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Tabla de pedidos -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
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
                    <tbody id="pedidos-table-body">
                        <!-- Los pedidos se cargarán aquí dinámicamente -->
                    </tbody>
                </table>
                
                <!-- Mensaje de carga -->
                <div id="loading-message" class="text-center py-3">
                    Cargando pedidos...
                </div>
                
                <!-- Paginación -->
                <div id="paginacion-container" class="mt-4">
                    <!-- La paginación se generará aquí -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script específico para pedidos -->
<script src="js/pedidos.js"></script>

<?php
// Incluir pie de página común
include_once 'footer.php';
?>