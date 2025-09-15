document.addEventListener('DOMContentLoaded', function() {
    console.log('Página de pedidos inicializada');
    
    // Elementos DOM
    const tablaPedidos = document.getElementById('pedidos-table-body') || document.querySelector('tbody');
    const loadingMessage = document.getElementById('loading-message') || document.querySelector('.cargando-pedidos');
    const estadoSelect = document.getElementById('estado');
    const fechaDesdeInput = document.getElementById('fecha_desde');
    const fechaHastaInput = document.getElementById('fecha_hasta');
    const filtrarBtn = document.getElementById('filtrar-btn');
    
    // Configurar los listeners de eventos
    if (filtrarBtn) {
        filtrarBtn.addEventListener('click', filtrarPedidos);
    }
    
    // Función para cargar pedidos con filtros
    function cargarPedidos(filtros = {}) {
        if (loadingMessage) {
            loadingMessage.textContent = 'Cargando pedidos...';
            loadingMessage.style.display = 'block';
        }
        
        // Construir URL con parámetros
        let url = 'api/pedidos.php?';
        for (const key in filtros) {
            if (filtros[key]) {
                url += `${key}=${encodeURIComponent(filtros[key])}&`;
            }
        }
        
        // Eliminar el último & si existe
        url = url.endsWith('&') ? url.slice(0, -1) : url;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (loadingMessage) {
                    loadingMessage.style.display = 'none';
                }
                
                if (data.pedidos && data.pedidos.length > 0) {
                    renderizarPedidos(data.pedidos);
                } else {
                    if (tablaPedidos) {
                        tablaPedidos.innerHTML = `<tr><td colspan="7" class="text-center">No hay pedidos que coincidan con los filtros</td></tr>`;
                    }
                }
                
                if (data.paginacion) {
                    renderizarPaginacion(data.paginacion);
                }
            })
            .catch(error => {
                console.error('Error al cargar pedidos:', error);
                if (loadingMessage) {
                    loadingMessage.textContent = 'Error al cargar pedidos. Intente nuevamente.';
                }
            });
    }
    
    // Función para renderizar los pedidos
    function renderizarPedidos(pedidos) {
        if (!tablaPedidos) return;
        
        tablaPedidos.innerHTML = '';
        
        pedidos.forEach(pedido => {
            const fecha = new Date(pedido.fecha);
            const fechaFormateada = fecha.toLocaleString('es-CO', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Clase para el estado
            let estadoClass = 'bg-secondary';
            if (pedido.estado === 'Pendiente') estadoClass = 'bg-warning';
            else if (pedido.estado === 'Completado') estadoClass = 'bg-success';
            else if (pedido.estado === 'Cancelado') estadoClass = 'bg-danger';
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>#${pedido.id}</td>
                <td>${pedido.cliente}</td>
                <td>${pedido.telefono || '-'}</td>
                <td>${fechaFormateada}</td>
                <td>$${formatCurrency(pedido.total || 0)}</td>
                <td><span class="badge ${estadoClass}">${pedido.estado}</span></td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="verPedido(${pedido.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="editarPedido(${pedido.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="cancelarPedido(${pedido.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            tablaPedidos.appendChild(tr);
        });
    }
    
    // Función para filtrar pedidos
    function filtrarPedidos(e) {
        if (e) e.preventDefault();
        
        const filtros = {
            estado: estadoSelect ? estadoSelect.value : null,
            fecha_desde: fechaDesdeInput ? fechaDesdeInput.value : null,
            fecha_hasta: fechaHastaInput ? fechaHastaInput.value : null,
            pagina: 1 // Siempre empezar en la primera página al filtrar
        };
        
        cargarPedidos(filtros);
    }
    
    // Función para renderizar paginación
    function renderizarPaginacion(paginacion) {
        const paginacionContainer = document.getElementById('paginacion-container');
        if (!paginacionContainer) return;
        
        paginacionContainer.innerHTML = '';
        
        if (paginacion.total_paginas <= 1) return;
        
        const ul = document.createElement('ul');
        ul.className = 'pagination justify-content-center';
        
        // Botón anterior
        const liPrev = document.createElement('li');
        liPrev.className = `page-item ${paginacion.pagina_actual <= 1 ? 'disabled' : ''}`;
        liPrev.innerHTML = `
            <a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${paginacion.pagina_actual - 1})" aria-label="Anterior">
                <span aria-hidden="true">&laquo;</span>
            </a>
        `;
        ul.appendChild(liPrev);
        
        // Números de página
        for (let i = 1; i <= paginacion.total_paginas; i++) {
            if (paginacion.total_paginas > 7) {
                // Lógica para mostrar menos páginas si hay muchas
                if (i !== 1 && i !== paginacion.total_paginas && 
                    (i < paginacion.pagina_actual - 1 || i > paginacion.pagina_actual + 1)) {
                    if (i === 2) {
                        const liEllipsis = document.createElement('li');
                        liEllipsis.className = 'page-item disabled';
                        liEllipsis.innerHTML = '<span class="page-link">...</span>';
                        ul.appendChild(liEllipsis);
                    }
                    if (i === paginacion.total_paginas - 1) {
                        const liEllipsis = document.createElement('li');
                        liEllipsis.className = 'page-item disabled';
                        liEllipsis.innerHTML = '<span class="page-link">...</span>';
                        ul.appendChild(liEllipsis);
                    }
                    continue;
                }
            }
            
            const li = document.createElement('li');
            li.className = `page-item ${paginacion.pagina_actual === i ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${i})">${i}</a>`;
            ul.appendChild(li);
        }
        
        // Botón siguiente
        const liNext = document.createElement('li');
        liNext.className = `page-item ${paginacion.pagina_actual >= paginacion.total_paginas ? 'disabled' : ''}`;
        liNext.innerHTML = `
            <a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${paginacion.pagina_actual + 1})" aria-label="Siguiente">
                <span aria-hidden="true">&raquo;</span>
            </a>
        `;
        ul.appendChild(liNext);
        
        paginacionContainer.appendChild(ul);
    }
    
    // Iniciar carga de pedidos
    cargarPedidos();
    
    // Exponer funciones globalmente
    window.cambiarPagina = function(pagina) {
        const filtros = {
            estado: estadoSelect ? estadoSelect.value : null,
            fecha_desde: fechaDesdeInput ? fechaDesdeInput.value : null,
            fecha_hasta: fechaHastaInput ? fechaHastaInput.value : null,
            pagina: pagina
        };
        
        cargarPedidos(filtros);
    };
});

// Función para formatear moneda
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-CO').format(amount);
}

// Función para ver detalles de un pedido
function verPedido(pedidoId) {
    console.log('Ver pedido:', pedidoId);
    
    fetch(`api/pedido_detalle.php?id=${pedidoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                mostrarModalDetallePedido(data.pedido);
            } else {
                mostrarNotificacion(data.message || 'Error al cargar los detalles del pedido', 'error');
            }
        })
        .catch(error => {
            console.error('Error al obtener detalles del pedido:', error);
            mostrarNotificacion('Error de conexión al servidor', 'error');
        });
}

// Función para editar un pedido
function editarPedido(pedidoId) {
    window.location.href = `pedido_update.php?id=${pedidoId}`;
}

// Función para cancelar un pedido
function cancelarPedido(pedidoId) {
    if (!confirm('¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.')) {
        return;
    }
    
    console.log('Cancelando pedido:', pedidoId);
    
    fetch(`api/pedido_cancelar.php?id=${pedidoId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data);
        
        if (data.status === 'success') {
            mostrarNotificacion('Pedido cancelado correctamente', 'success');
            
            // Recargar la página actual para reflejar el cambio
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            mostrarNotificacion(data.message || 'Error al cancelar el pedido', 'error');
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
        mostrarNotificacion('Error de conexión al servidor', 'error');
    });
}

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo = 'info') {
    // Verificar si existe la función global de notificación
    if (window.mostrarNotificacion) {
        window.mostrarNotificacion(mensaje, tipo);
        return;
    }
    
    // Si no existe una función global, crear una notificación básica
    const notificacion = document.createElement('div');
    notificacion.className = `alert alert-${tipo === 'error' ? 'danger' : tipo}`;
    notificacion.textContent = mensaje;
    notificacion.style.position = 'fixed';
    notificacion.style.top = '20px';
    notificacion.style.right = '20px';
    notificacion.style.zIndex = '9999';
    notificacion.style.maxWidth = '300px';
    
    document.body.appendChild(notificacion);
    
    // Eliminar después de 3 segundos
    setTimeout(() => {
        notificacion.remove();
    }, 3000);
}

// Función para mostrar el modal de detalle de pedido
function mostrarModalDetallePedido(pedido) {
    // Verificar si ya existe un modal
    let modal = document.getElementById('modal-detalle-pedido');
    
    // Si no existe el modal, crearlo
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'modal-detalle-pedido';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles del Pedido <span id="pedido-id"></span></h5>
                        <button type="button" class="close" onclick="cerrarModal()" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Información del Cliente</h6>
                                <p><strong>Nombre:</strong> <span id="cliente-nombre"></span></p>
                                <p><strong>Teléfono:</strong> <span id="cliente-telefono"></span></p>
                                <p><strong>Dirección:</strong> <span id="cliente-direccion"></span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Estado del Pedido</h6>
                                <select id="cambiar-estado" class="form-control mb-2">
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En preparación">En preparación</option>
                                    <option value="Listo">Listo</option>
                                    <option value="Entregado">Entregado</option>
                                    <option value="Cancelado">Cancelado</option>
                                </select>
                                <button onclick="guardarCambioEstado()" class="btn btn-success">Guardar Cambios</button>
                            </div>
                        </div>
                        <hr>
                        <h6>Productos</h6>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="items-pedido"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Total:</th>
                                        <th id="total-pedido"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="imprimirPedido()">Imprimir</button>
                        <button type="button" class="btn btn-danger" onclick="cancelarPedidoModal()">Cancelar Pedido</button>
                    </div>
                </div>
            </div>
        `;
        
        // Agregar estilos para el modal
        const estilosModal = document.createElement('style');
        estilosModal.textContent = `
            .modal {
                display: none;
                position: fixed;
                z-index: 1050;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.4);
            }
            .modal-dialog {
                margin: 50px auto;
            }
        `;
        document.head.appendChild(estilosModal);
        
        document.body.appendChild(modal);
    }
    
    // Llenar los datos del pedido
    document.getElementById('pedido-id').textContent = `#${pedido.id}`;
    document.getElementById('cliente-nombre').textContent = pedido.cliente.nombre;
    document.getElementById('cliente-telefono').textContent = pedido.cliente.telefono || 'No disponible';
    document.getElementById('cliente-direccion').textContent = pedido.cliente.direccion || 'No disponible';
    
    // Establecer el estado actual
    document.getElementById('cambiar-estado').value = pedido.estado;
    
    // Llenar tabla de productos
    const tbodyItems = document.getElementById('items-pedido');
    tbodyItems.innerHTML = '';
    
    let total = 0;
    
    pedido.items.forEach(item => {
        const subtotal = item.precio * item.cantidad;
        total += subtotal;
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.nombre}${item.descripcion ? '<br><small>' + item.descripcion + '</small>' : ''}</td>
            <td>${item.categoria}</td>
            <td>$${formatCurrency(item.precio)}</td>
            <td>${item.cantidad}</td>
            <td>$${formatCurrency(subtotal)}</td>
        `;
        
        tbodyItems.appendChild(tr);
    });
    
    // Actualizar total
    document.getElementById('total-pedido').textContent = `$${formatCurrency(total)}`;
    
    // Almacenar el ID del pedido actual para otras funciones
    modal.dataset.pedidoId = pedido.id;
    
    // Mostrar modal
    modal.style.display = 'block';
    
    // Función para cerrar el modal
    window.cerrarModal = function() {
        modal.style.display = 'none';
    };
    
    // Función para imprimir
    window.imprimirPedido = function() {
        const contenido = document.querySelector('.modal-content').cloneNode(true);
        contenido.querySelector('.modal-footer').remove();
        
        const ventanaImpresion = window.open('', '_blank');
        ventanaImpresion.document.write(`
            <html>
                <head>
                    <title>Pedido #${pedido.id}</title>
                    <link rel="stylesheet" href="css/bootstrap.min.css">
                    <style>
                        body { padding: 20px; }
                        @media print {
                            .modal-header button { display: none; }
                        }
                    </style>
                </head>
                <body onload="window.print(); window.close();">
                    ${contenido.outerHTML}
                </body>
            </html>
        `);
        ventanaImpresion.document.close();
    };
    
    // Función para cancelar pedido desde el modal
    window.cancelarPedidoModal = function() {
        const pedidoId = modal.dataset.pedidoId;
        cerrarModal();
        cancelarPedido(pedidoId);
    };
    
    // Función para guardar cambio de estado
    window.guardarCambioEstado = function() {
        const pedidoId = modal.dataset.pedidoId;
        const nuevoEstado = document.getElementById('cambiar-estado').value;
        
        fetch(`api/pedido_update.php?id=${pedidoId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                estado: nuevoEstado
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                mostrarNotificacion('Estado actualizado correctamente', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                mostrarNotificacion(data.message || 'Error al actualizar estado', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('Error de conexión al servidor', 'error');
        });
    };
}