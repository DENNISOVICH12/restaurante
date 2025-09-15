
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Inicializando vista de meseros...');
        
        // Elementos DOM principales
        const pedidosContainer = document.getElementById('pedidos-container');
        const filtros = document.querySelectorAll('.filtro-estado');
        let filtroActual = 'listo'; // Por defecto, mostrar pedidos listos
        
        // Cargar pedidos inicialmente y luego cada 30 segundos
        cargarPedidos();
        setInterval(cargarPedidos, 30000);
        
        // Configurar filtros
        filtros.forEach(btn => {
            btn.addEventListener('click', function() {
                filtros.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                filtroActual = this.getAttribute('data-estado');
                cargarPedidos();
            });
        });
        
        // Función para cargar pedidos
        function cargarPedidos() {
            console.log('Cargando pedidos para estado: ' + filtroActual);
            pedidosContainer.innerHTML = '<p>Cargando pedidos...</p>';
            
            fetch(`api/pedidos_meseros.php?estado=${filtroActual}`)
                .then(response => {
                    console.log('Respuesta recibida:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('Datos recibidos:', data);
                    pedidosContainer.innerHTML = '';
                    
                    if (!data || data.length === 0) {
                        pedidosContainer.innerHTML = '<p>No hay pedidos en este momento.</p>';
                        return;
                    }
                    
                    // Crear una cuadrícula para los pedidos
                    const grid = document.createElement('div');
                    grid.className = 'grid-container';
                    
                    data.forEach(pedido => {
                        const card = crearTarjetaPedido(pedido);
                        grid.appendChild(card);
                    });
                    
                    pedidosContainer.appendChild(grid);
                })
                .catch(error => {
                    console.error('Error al cargar pedidos:', error);
                    pedidosContainer.innerHTML = '<p>Error al cargar pedidos. Intente nuevamente.</p>';
                });
        }
        
        // Función para crear tarjeta de pedido
        function crearTarjetaPedido(pedido) {
            const card = document.createElement('div');
            card.className = `pedido-card ${pedido.estado}`;
            card.dataset.id = pedido.id;
            
            // Formatear hora
            const fecha = new Date(pedido.fecha);
            const hora = fecha.toLocaleTimeString('es-CO', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Crear contenido de la tarjeta
            card.innerHTML = `
                <div class="pedido-header">
                    <h3>Pedido #${pedido.id}</h3>
                    <span>${hora}</span>
                </div>
                <div class="pedido-estado">
                    <strong>Estado:</strong> ${formatearEstado(pedido.estado)}
                </div>
                <div class="pedido-cliente">
                    <strong>Cliente:</strong> ${pedido.cliente}
                </div>
                <div class="pedido-productos">
                    <strong>Productos:</strong>
                    <ul>
                        ${pedido.items.map(item => `
                            <li class="producto-item">
                                ${item.cantidad}x ${item.nombre}
                            </li>
                        `).join('')}
                    </ul>
                </div>
                <div class="pedido-actions">
                    ${pedido.estado === 'listo' ? 
                        `<button class="btn btn-primary btn-action" onclick="marcarComoEnEntrega(${pedido.id})">Iniciar entrega</button>` : 
                        ''}
                    ${pedido.estado === 'en_entrega' ? 
                        `<button class="btn btn-success btn-action" onclick="marcarComoEntregado(${pedido.id})">Completar entrega</button>` : 
                        ''}
                    <button class="btn btn-secondary btn-action" onclick="verDetalle(${pedido.id})">Ver detalles</button>
                </div>
            `;
            
            return card;
        }
        
        // Función para formatear estado
        function formatearEstado(estado) {
            const estados = {
                'pendiente': 'Pendiente',
                'en_preparacion': 'En preparación',
                'listo': 'Listo para entregar',
                'en_entrega': 'En entrega',
                'entregado': 'Entregado',
                'cancelado': 'Cancelado'
            };
            
            return estados[estado] || estado;
        }
        
        // Exponer funciones al ámbito global
        window.marcarComoEnEntrega = function(pedidoId) {
            console.log('Marcando pedido como en entrega:', pedidoId);
            actualizarEstadoPedido(pedidoId, 'en_entrega');
        };
        
        window.marcarComoEntregado = function(pedidoId) {
            console.log('Marcando pedido como entregado:', pedidoId);
            actualizarEstadoPedido(pedidoId, 'entregado');
        };
        
        window.verDetalle = function(pedidoId) {
            console.log('Viendo detalles del pedido:', pedidoId);
            // Aquí implementar vista de detalles
            alert('Ver detalles del pedido ' + pedidoId);
        };
        
        // Función para actualizar estado
        function actualizarEstadoPedido(pedidoId, nuevoEstado) {
            fetch(`api/pedido_update.php?id=${pedidoId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ estado: nuevoEstado })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Estado actualizado correctamente');
                    cargarPedidos(); // Recargar para reflejar cambios
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar el estado'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al comunicarse con el servidor');
            });
        }
    });
