/**
 * Funciones para la gestión de pedidos
 * pedidos.js
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de pedidos inicializado');
    
    // Añadir event listener al botón de guardar estado en el modal de detalles
    const btnGuardarEstado = document.getElementById('guardar-estado');
    if (btnGuardarEstado) {
        btnGuardarEstado.addEventListener('click', function() {
            const pedidoId = document.getElementById('pedido-id').textContent.replace('#', '');
            const nuevoEstado = document.getElementById('cambiar-estado').value;
            actualizarEstadoPedido(pedidoId, nuevoEstado);
        });
    }
    
    // Añadir event listener al botón de cancelar pedido en el modal
    const btnCancelarPedido = document.getElementById('cancelar-pedido');
    if (btnCancelarPedido) {
        btnCancelarPedido.addEventListener('click', function() {
            const pedidoId = document.getElementById('pedido-id').textContent.replace('#', '');
            // Usamos confirm() directamente para evitar problemas con modales anidados
            if (confirm('¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.')) {
                // Cerrar el modal de detalles
                document.getElementById('modal-detalle-pedido').style.display = 'none';
                // Cancelar el pedido
                cancelarPedido(pedidoId);
            }
        });
    }
    
    // Botón para imprimir pedido
    const btnImprimirPedido = document.getElementById('imprimir-pedido');
    if (btnImprimirPedido) {
        btnImprimirPedido.addEventListener('click', function() {
            imprimirPedidoActual();
        });
    }
    
    // Añadir event listener para eliminar pedido permanentemente (solo para administradores)
    const btnEliminarPedido = document.getElementById('eliminar-pedido');
    if (btnEliminarPedido) {
        btnEliminarPedido.addEventListener('click', function() {
            const pedidoId = document.getElementById('pedido-id').textContent.replace('#', '');
            eliminarPedido(pedidoId);
        });
    }
});

/**
 * Función para actualizar el estado de un pedido
 */
function actualizarEstadoPedido(pedidoId, nuevoEstado) {
    console.log(`Actualizando pedido ${pedidoId} a estado: ${nuevoEstado}`);
    
    // Mostrar indicador de carga
    const btnGuardar = document.getElementById('guardar-estado');
    const textoOriginal = btnGuardar.textContent;
    btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
    btnGuardar.disabled = true;
    
    // Enviar solicitud AJAX
    fetch(`api/pedido_update.php?id=${pedidoId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(response => response.json())
    .then(data => {
        btnGuardar.textContent = textoOriginal;
        btnGuardar.disabled = false;
        
        if (data.status === 'success') {
            mostrarNotificacion('Estado actualizado correctamente', 'success');
            
            // Cerrar el modal
            document.getElementById('modal-detalle-pedido').style.display = 'none';
            
            // Actualizar dashboard 
            if (typeof actualizarDashboard === 'function') {
                actualizarDashboard();
            }
        } else {
            mostrarNotificacion(data.message || 'Error al actualizar estado', 'error');
        }
    })
    .catch(error => {
        console.error('Error al actualizar estado:', error);
        btnGuardar.textContent = textoOriginal;
        btnGuardar.disabled = false;
        mostrarNotificacion('Error al comunicarse con el servidor', 'error');
    });
}

/**
 * Función para cancelar un pedido
 */
function cancelarPedido(pedidoId) {
    // Mostrar indicador de carga
    mostrarCargando();
    
    console.log(`Cancelando pedido ID: ${pedidoId}`);
    
    // Enviar solicitud AJAX
    fetch(`api/pedido_cancelar.php?id=${pedidoId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log("Respuesta recibida:", response);
        return response.json();
    })
    .then(data => {
        console.log("Datos de respuesta:", data);
        ocultarCargando();
        
        if (data.status === 'success') {
            mostrarNotificacion('Pedido cancelado correctamente', 'success');
            
            // Actualizar dashboard
            if (typeof actualizarDashboard === 'function') {
                actualizarDashboard();
            }
        } else {
            mostrarNotificacion(data.message || 'Error al cancelar pedido', 'error');
        }
    })
    .catch(error => {
        ocultarCargando();
        console.error('Error al cancelar pedido:', error);
        mostrarNotificacion('Error al comunicarse con el servidor', 'error');
    });
}

/**
 * Función para eliminar permanentemente un pedido
 */
function eliminarPedido(pedidoId) {
    if (!confirm('¿Estás COMPLETAMENTE SEGURO de eliminar permanentemente este pedido? Esta acción NO SE PUEDE DESHACER y eliminará todos los datos asociados al pedido.')) {
        return;
    }
    
    // Segunda confirmación como medida de seguridad
    if (!confirm('ADVERTENCIA: Esta acción eliminará el pedido de la base de datos permanentemente. ¿Deseas continuar?')) {
        return;
    }
    
    // Mostrar indicador de carga
    mostrarCargando();
    
    console.log(`Eliminando permanentemente el pedido ID: ${pedidoId}`);
    
    // Enviar solicitud AJAX
    fetch(`api/pedido_eliminar.php?id=${pedidoId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log("Respuesta recibida:", response);
        return response.json();
    })
    .then(data => {
        console.log("Datos de respuesta:", data);
        ocultarCargando();
        
        if (data.status === 'success') {
            mostrarNotificacion('Pedido eliminado permanentemente', 'success');
            
            // Cerrar el modal si está abierto
            const modal = document.getElementById('modal-detalle-pedido');
            if (modal && modal.style.display === 'block') {
                modal.style.display = 'none';
            }
            
            // Actualizar dashboard
            if (typeof actualizarDashboard === 'function') {
                actualizarDashboard();
            }
        } else {
            mostrarNotificacion(data.message || 'Error al eliminar pedido', 'error');
        }
    })
    .catch(error => {
        ocultarCargando();
        console.error('Error al eliminar pedido:', error);
        mostrarNotificacion('Error al comunicarse con el servidor', 'error');
    });
}

/**
 * Función para imprimir el pedido actual
 */
/**
 * Función para imprimir el pedido actual
 */
function imprimirPedidoActual() {
    // Obtener información del pedido del modal
    const pedidoId = document.getElementById('pedido-id').textContent;
    const clienteNombre = document.getElementById('cliente-nombre').textContent;
    const clienteTelefono = document.getElementById('cliente-telefono').textContent;
    const clienteDireccion = document.getElementById('cliente-direccion').textContent;
    const estado = document.getElementById('cambiar-estado').value;
    const total = document.getElementById('total-pedido').textContent;
    
    // Clonar la tabla de productos para manipularla
    const tablaProductos = document.getElementById('items-pedido').cloneNode(true);
    
    // Formatear la fecha actual
    const fechaActual = new Date();
    const fechaFormateada = fechaActual.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Crear una ventana de impresión
    const ventanaImpresion = window.open('', '_blank');
    ventanaImpresion.document.write(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Pedido ${pedidoId} - Restaurante</title>
            <style>
                /* Estilos para la impresión */
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    color: #333;
                    line-height: 1.5;
                }
                
                .logo {
                    text-align: center;
                    margin-bottom: 20px;
                    font-size: 24px;
                    font-weight: bold;
                }
                
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #333;
                    padding-bottom: 10px;
                }
                
                h1 {
                    font-size: 22px;
                    margin: 0 0 10px 0;
                }
                
                .fecha {
                    font-size: 14px;
                    margin: 5px 0;
                }
                
                .info-seccion {
                    margin-bottom: 20px;
                }
                
                .info-seccion h2 {
                    font-size: 18px;
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 5px;
                    margin-bottom: 10px;
                }
                
                .info-cliente {
                    display: flex;
                    flex-wrap: wrap;
                }
                
                .info-cliente p {
                    margin: 5px 0;
                    width: 100%;
                }
                
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                
                table, th, td {
                    border: 1px solid #ddd;
                }
                
                th {
                    background-color: #f2f2f2;
                    padding: 10px;
                    text-align: left;
                    font-weight: bold;
                }
                
                td {
                    padding: 8px;
                }
                
                .total-row {
                    font-weight: bold;
                    text-align: right;
                }
                
                .total-cell {
                    font-weight: bold;
                }
                
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 12px;
                    color: #777;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
                
                .acciones {
                    text-align: center;
                    margin-top: 20px;
                }
                
                .btn {
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    margin: 5px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 14px;
                }
                
                .btn-secondary {
                    background-color: #6c757d;
                }
                
                @media print {
                    .acciones {
                        display: none;
                    }
                    
                    body {
                        margin: 0;
                        padding: 15px;
                    }
                    
                    @page {
                        size: auto;
                        margin: 10mm;
                    }
                }
            </style>
        </head>
        <body>
            <div class="logo">RESTAURANTE</div>
            
            <div class="header">
                <h1>Comprobante de Pedido ${pedidoId}</h1>
                <p class="fecha">Fecha de emisión: ${fechaFormateada}</p>
            </div>
            
            <div class="info-seccion">
                <h2>Información del Cliente</h2>
                <div class="info-cliente">
                    <p><strong>Nombre:</strong> ${clienteNombre}</p>
                    <p><strong>Teléfono:</strong> ${clienteTelefono}</p>
                    <p><strong>Dirección:</strong> ${clienteDireccion || 'No especificada'}</p>
                </div>
            </div>
            
            <div class="info-seccion">
                <h2>Estado del Pedido</h2>
                <p><strong>Estado actual:</strong> ${formatearEstado(estado)}</p>
            </div>
            
            <div class="info-seccion">
                <h2>Productos</h2>
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
                    <tbody>
                        ${document.getElementById('items-pedido').innerHTML}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="total-row"><strong>Total:</strong></td>
                            <td class="total-cell">${total}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="footer">
                <p>Gracias por su preferencia</p>
                <p>RESTAURANTE | Impreso el ${fechaFormateada}</p>
            </div>
            
            <div class="acciones">
                <button class="btn" onclick="window.print()">Imprimir</button>
                <button class="btn btn-secondary" onclick="window.close()">Cerrar</button>
            </div>
            
            <script>
                // Imprimir automáticamente cuando la página esté lista
                window.onload = function() {
                    // Pequeño retraso para permitir que se carguen los estilos
                    setTimeout(function() {
                        window.print();
                    }, 1000);
                };
            </script>
        </body>
        </html>
    `);
    
    // Cerrar el documento para finalizar la escritura
    ventanaImpresion.document.close();
}
/**
 * Funciones auxiliares
 */
function mostrarCargando() {
    // Implementación de indicador de carga
    const cargando = document.createElement('div');
    cargando.id = 'cargando-overlay';
    cargando.innerHTML = `
        <div class="cargando-contenido">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <span>Procesando...</span>
        </div>
    `;
    
    // Estilos
    cargando.style.position = 'fixed';
    cargando.style.top = '0';
    cargando.style.left = '0';
    cargando.style.width = '100%';
    cargando.style.height = '100%';
    cargando.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    cargando.style.display = 'flex';
    cargando.style.justifyContent = 'center';
    cargando.style.alignItems = 'center';
    cargando.style.zIndex = '9999';
    
    // Agregar a la página
    document.body.appendChild(cargando);
}

function ocultarCargando() {
    const cargando = document.getElementById('cargando-overlay');
    if (cargando) {
        cargando.remove();
    }
}

function formatearEstado(estado) {
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

function mostrarNotificacion(mensaje, tipo = 'success') {
    // Verificar si la función ya existe globalmente
    if (typeof window.mostrarNotificacion === 'function') {
        window.mostrarNotificacion(mensaje, tipo);
        return;
    }
    
    // Si no existe, implementar la función
    // Crear elemento de notificación
    const notificacion = document.createElement('div');
    notificacion.className = `notificacion ${tipo}`;
    notificacion.innerHTML = `
        <div class="notificacion-content">
            <i class="fa-solid ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${mensaje}</span>
        </div>
        <button class="cerrar-notificacion">&times;</button>
    `;
    
    // Estilos
    notificacion.style.position = 'fixed';
    notificacion.style.top = '20px';
    notificacion.style.right = '20px';
    notificacion.style.backgroundColor = tipo === 'success' ? '#4CAF50' : '#F44336';
    notificacion.style.color = 'white';
    notificacion.style.padding = '15px';
    notificacion.style.borderRadius = '4px';
    notificacion.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    notificacion.style.zIndex = '9999';
    notificacion.style.minWidth = '250px';
    
    // Agregar a la página
    document.body.appendChild(notificacion);
    
    // Configurar botón para cerrar
    notificacion.querySelector('.cerrar-notificacion').addEventListener('click', function() {
        notificacion.remove();
    });
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        if (document.body.contains(notificacion)) {
            notificacion.remove();
        }
    }, 5000);
}

// Exponer funciones globalmente para que puedan ser llamadas desde otros scripts o eventos onclick
window.actualizarEstadoPedido = actualizarEstadoPedido;
window.cancelarPedido = cancelarPedido;
window.eliminarPedido = eliminarPedido;
window.imprimirPedidoActual = imprimirPedidoActual;