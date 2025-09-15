/**
 * Dashboard JavaScript principal
 * Para la gestión del restaurante
 */

document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentConfirmCallback = null;
    const apiEndpoint = 'api/';

    // Elementos DOM
    const menuItems = document.querySelectorAll('.menu ul li');
    const pages = document.querySelectorAll('.page');
    const logoutBtn = document.getElementById('logout');
    const modalDetallesPedido = document.getElementById('modal-detalle-pedido');
    const modalItemMenu = document.getElementById('modal-item-menu');
    const modalUsuario = document.getElementById('modal-usuario');
    const modalConfirmacion = document.getElementById('modal-confirmacion');
    const closeButtons = document.querySelectorAll('.close');
    
    // Elementos DOM para gestión de menú
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Navegación del menú lateral
    menuItems.forEach(item => {
        if (!item.id) { // Excluir el botón de logout
            item.addEventListener('click', function() {
                // Remover clase active de todos los elementos
                menuItems.forEach(mi => mi.classList.remove('active'));
                
                // Agregar clase active al elemento clickeado
                this.classList.add('active');
                
                // Mostrar la página correspondiente
                const pageId = this.getAttribute('data-page');
                
                // Actualizar título de la página
                document.querySelector('.page-title h1').textContent = this.querySelector('span').textContent;
                
                // Ocultar todas las páginas y mostrar la seleccionada
                pages.forEach(page => {
                    page.classList.remove('active');
                    if (page.id === pageId) {
                        page.classList.add('active');
                    }
                });
            });
        }
    });
    
    // Cerrar sesión
    logoutBtn.addEventListener('click', function() {
        window.location.href = 'logout.php';
    });
    
    // Manejo de pestañas en menú
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Activar la pestaña seleccionada
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Mostrar el contenido correspondiente
            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === 'tab-' + tabName) {
                    content.classList.add('active');
                }
            });
        });
    });
    
    // Cerrar modales
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            cerrarModal(modal);
        });
    });
    
    // Cerrar al hacer clic fuera del modal
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            cerrarModal(event.target);
        }
    });
    
    /**
     * Abre un modal
     */
    function abrirModal(modal) {
        modal.style.display = 'block';
    }
    
    /**
     * Cierra un modal
     */
    function cerrarModal(modal) {
        modal.style.display = 'none';
    }
    
    /**
     * Muestra un mensaje de confirmación
     */
    function mostrarConfirmacion(mensaje, callback) {
        document.getElementById('mensaje-confirmacion').textContent = mensaje;
        currentConfirmCallback = callback;
        abrirModal(modalConfirmacion);
    }
    
    /**
     * Muestra una notificación
     */
    function mostrarNotificacion(mensaje, tipo = 'success') {
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
        
        // Agregar a la página
        document.body.appendChild(notificacion);
        
        // Mostrar con animación
        setTimeout(() => {
            notificacion.classList.add('mostrar');
        }, 10);
        
        // Configurar botón para cerrar
        notificacion.querySelector('.cerrar-notificacion').addEventListener('click', function() {
            notificacion.classList.remove('mostrar');
            setTimeout(() => {
                notificacion.remove();
            }, 300);
        });
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            if (document.body.contains(notificacion)) {
                notificacion.classList.remove('mostrar');
                setTimeout(() => {
                    if (document.body.contains(notificacion)) {
                        notificacion.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
});