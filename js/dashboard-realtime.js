/**
 * Script para actualizar en tiempo real los datos del dashboard
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard en tiempo real inicializado');
    
    // Elementos DOM
    const pedidosPendientesElement = document.getElementById('pedidos-pendientes');
    const ventasDiaElement = document.getElementById('ventas-dia');
    const clientesNuevosElement = document.getElementById('clientes-nuevos');
    const platoPupularElement = document.getElementById('plato-popular');
    const recentOrdersElement = document.getElementById('recent-orders');
    
    // Títulos
    const ventasTituloElement = document.querySelector('.widget:nth-child(2) h3');
    const clientesTituloElement = document.querySelector('.widget:nth-child(3) h3');
    
    // Intervalo de actualización en milisegundos (30 segundos)
    const updateInterval = 30000;
    
    // Período actual (hoy o mes)
    let periodoActual = 'mes'; // Comenzar con datos del mes para tener algo que mostrar
    
    // Agregar botón para cambiar período
    const widgets = document.querySelector('.widgets');
    if (widgets) {
        const cambiarPeriodoBtn = document.createElement('button');
        cambiarPeriodoBtn.className = 'btn btn-primary cambiar-periodo';
        cambiarPeriodoBtn.innerHTML = 'Ver datos de hoy';
        cambiarPeriodoBtn.style.marginTop = '10px';
        widgets.insertAdjacentElement('afterend', cambiarPeriodoBtn);
        
        // Evento para cambiar período
        cambiarPeriodoBtn.addEventListener('click', function() {
            periodoActual = periodoActual === 'hoy' ? 'mes' : 'hoy';
            this.innerHTML = periodoActual === 'hoy' ? 'Ver datos del mes' : 'Ver datos de hoy';
            actualizarDashboard();
        });
    }
    
    // Función para formatear moneda
    function formatCurrency(amount) {
        return '$' + parseFloat(amount).toLocaleString('es-CO');
    }
    
    // Función para formatear fecha
    function formatDate(dateString) {
        const options = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        };
        return new Date(dateString).toLocaleDateString('es-ES', options);
    }
    
    // Función para formatear estado
    function formatEstado(estado) {
        const estados = {
            'pendiente': 'Pendiente',
            'en_preparacion': 'En preparación',
            'listo': 'Listo',
            'en_entrega': 'En entrega',
            'entregado': 'Entregado',
            'cancelado': 'Cancelado'
        };
        
        return estados[estado] || estado;
    }
    
    // Función para actualizar los datos del dashboard
    function actualizarDashboard() {
        console.log('Actualizando datos del dashboard...');
        
        fetch(`api/dashboard_data.php?periodo=${periodoActual}`)
            .then(response => response.json())
            .then(data => {
                console.log('Datos actualizados:', data);
                
                // Actualizar widgets
                if (pedidosPendientesElement) {
                    pedidosPendientesElement.textContent = data.pedidos_pendientes;
                }
                
                if (ventasDiaElement) {
                    ventasDiaElement.textContent = formatCurrency(data.ventas_dia);
                }
                
                if (clientesNuevosElement) {
                    clientesNuevosElement.textContent = data.clientes_nuevos;
                }
                
                if (platoPupularElement) {
                    platoPupularElement.textContent = data.plato_popular;
                }
                
                // Actualizar títulos
                if (ventasTituloElement && data.titulo_ventas) {
                    ventasTituloElement.textContent = data.titulo_ventas;
                }
                
                if (clientesTituloElement && data.titulo_clientes) {
                    clientesTituloElement.textContent = data.titulo_clientes;
                }
                
                // Actualizar tabla de pedidos recientes
                if (recentOrdersElement) {
                    // Solo actualizar si estamos en la página de inicio
                    if (document.querySelector('#dashboard-home').classList.contains('active')) {
                        actualizarTablaPedidosRecientes(data.pedidos_recientes);
                    }
                }
            })
            .catch(error => {
                console.error('Error al actualizar datos del dashboard:', error);
            });
    }
    
    // Función para actualizar la tabla de pedidos recientes

    // Función para actualizar la tabla de pedidos recientes
function actualizarTablaPedidosRecientes(pedidos) {
    if (!recentOrdersElement) return;
    
    // Limpiar tabla
    recentOrdersElement.innerHTML = '';
    
    if (!pedidos || pedidos.length === 0) {
        recentOrdersElement.innerHTML = '<tr><td colspan="6">No hay pedidos recientes</td></tr>';
        return;
    }
    
    // Agregar filas de pedidos
    pedidos.forEach(pedido => {
        const tr = document.createElement('tr');
        
        // Determinar si mostrar el botón de cancelar (solo para pedidos no cancelados)
        const botonCancelar = pedido.estado !== 'cancelado' ? 
            `<button class="btn btn-danger btn-sm" onclick="cancelarPedido(${pedido.id})">
                <i class="fa-solid fa-ban"></i>
            </button>` : '';
        
        tr.innerHTML = `
            <td>#${pedido.id}</td>
            <td>${pedido.cliente}</td>
            <td>${formatDate(pedido.fecha)}</td>
            <td>${formatCurrency(pedido.total)}</td>
            <td><span class="status ${pedido.estado}">${formatEstado(pedido.estado)}</span></td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-primary btn-sm" onclick="verDetallePedido(${pedido.id})">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    ${botonCancelar}
                </div>
            </td>
        `;
        
        recentOrdersElement.appendChild(tr);
    });
}


    // Iniciar actualización inmediata
    actualizarDashboard();
    
    // Configurar intervalo de actualización
    setInterval(actualizarDashboard, updateInterval);
    
    // Actualizar también cuando se haga clic en el botón de inicio
    document.querySelector('.menu ul li[data-page="dashboard-home"]').addEventListener('click', function() {
        actualizarDashboard();
    });

    // Función para cancelar un pedido
    window.cancelarPedido = function(pedidoId) {
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
            console.log('Respuesta al cancelar pedido:', data);
            
            if (data.status === 'success') {
                mostrarNotificacion(data.message, 'success');
                
                // Actualizar datos del dashboard
                actualizarDashboard();
            } else {
                mostrarNotificacion(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error al cancelar pedido:', error);
            mostrarNotificacion('Error al cancelar pedido: ' + error.message, 'error');
        });
    };
    
    // Función para ver detalles de un pedido
    window.verDetallePedido = function(pedidoId) {
        console.log('Ver detalles del pedido:', pedidoId);
        
        fetch(`api/pedido_detalle.php?id=${pedidoId}`)
            .then(response => response.json())
            .then(data => {
                console.log('Datos del pedido:', data);
                
                if (data.status === 'success') {
                    const pedido = data.pedido;
                    
                    // Mostrar modal de detalles
                    const modal = document.getElementById('modal-detalle-pedido');
                    if (modal) {
                        document.getElementById('pedido-id').textContent = pedido.id;
                        document.getElementById('cliente-nombre').textContent = pedido.cliente.nombre;
                        document.getElementById('cliente-telefono').textContent = pedido.cliente.telefono;
                        document.getElementById('cliente-direccion').textContent = pedido.cliente.direccion || 'No disponible';
                        
                        // Actualizar selector de estado
                        document.getElementById('cambiar-estado').value = pedido.estado;
                        
                        // Actualizar tabla de productos
                        const tbody = document.getElementById('items-pedido');
                        tbody.innerHTML = '';
                        
                        let total = 0;
                        
                        pedido.items.forEach(item => {
                            const subtotal = item.precio * item.cantidad;
                            total += subtotal;
                            
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${item.nombre}${item.descripcion ? '<br><small>' + item.descripcion + '</small>' : ''}</td>
                                <td>${item.categoria}</td>
                                <td>${formatCurrency(item.precio)}</td>
                                <td>${item.cantidad}</td>
                                <td>${formatCurrency(subtotal)}</td>
                            `;
                            
                            tbody.appendChild(tr);
                        });
                        
                        // Actualizar total
                        document.getElementById('total-pedido').textContent = formatCurrency(total);
                        
                        // Mostrar modal
                        modal.style.display = 'block';
                    } else {
                        // Si no hay modal de detalles, mostrar un mensaje
                        alert(`Pedido #${pedido.id}\nCliente: ${pedido.cliente.nombre}\nEstado: ${formatEstado(pedido.estado)}\nTotal: ${formatCurrency(pedido.total || 0)}`);
                    }
                } else {
                    mostrarNotificacion(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error al obtener detalles del pedido:', error);
                mostrarNotificacion('Error al obtener detalles del pedido: ' + error.message, 'error');
            });
    };

});