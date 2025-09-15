/**
 * js/cocina.js - Funcionalidades JavaScript para la vista de cocina
 */
document.addEventListener('DOMContentLoaded', function() {
    const pedidosContainer = document.getElementById('pedidos-container');
    const filtros = document.querySelectorAll('.filtro-estado');
    let filtroActual = 'todos';
    const modalDetalles = document.getElementById('modal-detalle-pedido');
    
    // Cargar pedidos inicialmente y luego cada 30 segundos
    cargarPedidos();
    const intervalId = setInterval(cargarPedidos, 30000);
    
    // Configurar filtros
    filtros.forEach(btn => {
        btn.addEventListener('click', function() {
            filtros.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filtroActual = this.getAttribute('data-estado');
            cargarPedidos();
        });
    });
    
    // Configurar cierre de modal
    document.querySelector('.close').addEventListener('click', function() {
        modalDetalles.style.display = 'none';
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target === modalDetalles) {
            modalDetalles.style.display = 'none';
        }
    });
    
    // Configurar botón de guardar estado en el modal
    document.getElementById('guardar-estado').addEventListener('click', function() {
        const pedidoId = document.getElementById('pedido-id').textContent;
        const nuevoEstado = document.getElementById('cambiar-estado').value;
        actualizarEstadoPedido(pedidoId, nuevoEstado);
    });
    
    // Función para cargar pedidos
    function cargarPedidos() {
        console.log('Cargando pedidos, filtro:', filtroActual);
        
        fetch(`api/pedidos_cocina.php?estado=${filtroActual}`)
            .then(response => response.json())
            .then(data => {
                console.log('Datos recibidos:', data);
                pedidosContainer.innerHTML = '';
                
                if (!data || data.length === 0) {
                    pedidosContainer.innerHTML = '<p>No hay pedidos en este momento.</p>';
                    return;
                }
                
                data.forEach(pedido => {
                    const card = crearTarjetaPedido(pedido);
                    pedidosContainer.appendChild(card);
                });
            })
            .catch(error => {
                console.error('Error al cargar pedidos:', error);
                pedidosContainer.innerHTML = '<p>Error al cargar pedidos. Intente nuevamente.</p>';
            });
    }
    
    // Función para crear tarjeta de pedido
    function crearTarjetaPedido(pedido) {
        const card = document.createElement('div');
        // Convertir el nombre del estado a una clase CSS válida
        let estadoClass = pedido.estado.toLowerCase().replace('_', '-');
        
        // Compatibilidad con estados antiguos
        if (estadoClass === 'pendiente') {
            estadoClass = 'pendiente';
        } else if (estadoClass === 'en-preparacion' || estadoClass === 'en-preparacion') {
            estadoClass = 'en-preparacion';
        } else if (estadoClass === 'listo') {
            estadoClass = 'listo';
        }
        
        card.className = `pedido-card ${estadoClass}`;
        card.dataset.id = pedido.id;
        
        // Formatear hora
        const fecha = new Date(pedido.fecha);
        const hora = fecha.toLocaleTimeString('es-CO', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Determinar los botones de acción según el estado
        let botonesAccion = '';
        
        if (pedido.estado === 'pendiente' || pedido.estado === 'pendiente') {
            botonesAccion = `<a href="cambiar_estado.php?id=${pedido.id}&estado=en_preparacion" class="btn btn-primary btn-action">Comenzar preparación</a>`;
        } else if (pedido.estado === 'en_preparacion' || pedido.estado === 'en_preparacion') {
            botonesAccion = `<a href="cambiar_estado.php?id=${pedido.id}&estado=listo" class="btn btn-success btn-action">Marcar como listo</a>`;
        }
        
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
                            ${item.descripcion ? `<small>(${item.descripcion})</small>` : ''}
                        </li>
                    `).join('')}
                </ul>
            </div>
            <div class="pedido-actions">
                ${botonesAccion}
                <button class="btn btn-secondary btn-action" onclick="verDetallePedido(${pedido.id})">Ver detalles</button>
            </div>
        `;
        
        return card;
    }
    
    // Función para formatear estado
    function formatearEstado(estado) {
        const estados = {
            'pendiente': 'Pendiente',
            'en_preparacion': 'En preparación',
            'listo': 'Listo',
            'pendiente': 'Pendiente',
            'en_entrega': 'En entrega',
            'entregado': 'Entregado',
            'cancelado': 'Cancelado'
        };
        
        return estados[estado] || estado;
    }
    
    // Función para mostrar/ocultar indicador de carga
    function mostrarCargando() {
        const cargando = document.createElement('div');
        cargando.id = 'cargando-overlay';
        cargando.innerHTML = `
            <div class="cargando-contenido">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Procesando...</span>
            </div>
        `;
        document.body.appendChild(cargando);
    }
    
    function ocultarCargando() {
        const cargando = document.getElementById('cargando-overlay');
        if (cargando) cargando.remove();
    }
    
    // Función para mostrar notificaciones
    function mostrarNotificacion(mensaje, tipo = 'success') {
        const notificacion = document.createElement('div');
        notificacion.className = `notificacion ${tipo}`;
        notificacion.innerHTML = `
            <div class="notificacion-content">
                <span>${mensaje}</span>
            </div>
        `;
        
        document.body.appendChild(notificacion);
        
        setTimeout(() => {
            notificacion.remove();
        }, 3000);
    }
    
    // Exponer funciones al ámbito global
    window.verDetallePedido = function(pedidoId) {
        console.log('Ver detalles del pedido:', pedidoId);
        
        fetch(`api/pedido_detalle.php?id=${pedidoId}`)
            .then(response => response.json())
            .then(data => {
                console.log('Datos del pedido:', data);
                
                if (data.status === 'success') {
                    const pedido = data.pedido;
                    
                    // Llenar y mostrar modal
                    document.getElementById('pedido-id').textContent = pedido.id;
                    document.getElementById('cliente-nombre').textContent = pedido.cliente.nombre;
                    document.getElementById('cliente-telefono').textContent = pedido.cliente.telefono;
                    document.getElementById('cliente-direccion').textContent = pedido.cliente.direccion || 'No disponible';
                    document.getElementById('cambiar-estado').value = pedido.estado;
                    
                    // Llenar tabla de productos
                    const tbody = document.getElementById('items-pedido');
                    tbody.innerHTML = '';
                    
                    pedido.items.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${item.nombre}</td>
                            <td>${item.cantidad}</td>
                            <td>${item.descripcion || '-'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                    
                    // Mostrar modal
                    modalDetalles.style.display = 'block';
                } else {
                    mostrarNotificacion(data.message || 'Error al cargar detalles', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('Error al comunicarse con el servidor', 'error');
            });
    };
    
    // La función actualizarEstadoPedido la mantenemos para el botón del modal
    window.actualizarEstadoPedido = function(pedidoId, nuevoEstado) {
        console.log(`Actualizando estado del pedido ${pedidoId} a ${nuevoEstado}`);
        
        // Mostrar indicador de carga
        mostrarCargando();
        
        // Enviar solicitud AJAX
        fetch(`api/pedido_update.php?id=${pedidoId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ estado: nuevoEstado })
        })
        .then(response => {
            console.log('Respuesta recibida:', response);
            
            // Verificar si la respuesta es un JSON válido
            return response.text().then(text => {
                console.log('Respuesta como texto:', text);
                try {
                    return JSON.parse(text);
                } catch (error) {
                    console.error('Error al parsear JSON:', error);
                    console.error('Texto recibido:', text);
                    throw new Error('Respuesta del servidor no es un JSON válido');
                }
            });
        })
        .then(data => {
            console.log('Datos de respuesta:', data);
            ocultarCargando();
            
            if (data.status === 'success') {
                mostrarNotificacion('Estado actualizado correctamente', 'success');
                
                // Cerrar el modal si está abierto
                if (modalDetalles.style.display === 'block') {
                    modalDetalles.style.display = 'none';
                }
                
                // Recargar pedidos para mostrar cambios
                cargarPedidos();
            } else {
                mostrarNotificacion(data.message || 'Error al actualizar estado', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ocultarCargando();
            mostrarNotificacion('Error al comunicarse con el servidor: ' + error.message, 'error');
        });
    };
});