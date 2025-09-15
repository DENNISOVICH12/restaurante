/**
 * Script para mostrar los elementos del menú en el dashboard con un estilo visual atractivo
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verificación de la carga del script
    console.log("Script menu-display.js cargado correctamente");
    
    // Elementos DOM
    const tabPlatos = document.getElementById('tab-platos');
    const tabBebidas = document.getElementById('tab-bebidas');
    const tabPostres = document.getElementById('tab-postres');
    
    console.log("Elementos DOM:", {
        tabPlatos: tabPlatos,
        tabBebidas: tabBebidas,
        tabPostres: tabPostres
    });
    
    // Cargar datos iniciales
    cargarPlatos();
    
    // Evento para botones de tab
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function() {
            console.log("Tab cliqueado:", this.getAttribute('data-tab'));
            
            const categoria = this.getAttribute('data-tab');
            switch(categoria) {
                case 'platos':
                    cargarPlatos();
                    break;
                case 'bebidas':
                    cargarBebidas();
                    break;
                case 'postres':
                    cargarPostres();
                    break;
            }
        });
    });
    
    // Botón para nuevo ítem
    document.getElementById('nuevo-item-menu').addEventListener('click', function() {
        console.log("Botón nuevo ítem cliqueado");
        
        // Limpiar formulario
        document.getElementById('form-item-menu').reset();
        document.getElementById('item-id').value = '';
        document.getElementById('preview-imagen').innerHTML = '';
        document.getElementById('titulo-modal-item').textContent = 'Nuevo Ítem del Menú';
        
        // Determinar categoría según tab activo
        const tabActivo = document.querySelector('.tab.active').getAttribute('data-tab');
        const categoria = tabActivo === 'postres' ? 'postre' : tabActivo === 'bebidas' ? 'bebida' : 'plato';
        console.log("Categoría seleccionada:", categoria);
        
        document.getElementById('item-categoria').value = categoria;
        
        // Mostrar modal
        document.getElementById('modal-item-menu').style.display = 'block';
    });
    
    /**
     * Carga y muestra los platos
     */
    function cargarPlatos() {
        console.log("Cargando platos...");
        
        fetch('api/menu.php?categoria=plato')
            .then(response => {
                console.log("Respuesta del servidor:", response);
                return response.json();
            })
            .then(data => {
                console.log("Datos de platos recibidos:", data);
                mostrarItemsMenuVisual(data.items, tabPlatos);
            })
            .catch(error => {
                console.error('Error al cargar platos:', error);
                tabPlatos.innerHTML = '<div class="error-message">Error al cargar platos: ' + error.message + '</div>';
            });
    }
    
    /**
     * Carga y muestra las bebidas
     */
    function cargarBebidas() {
        console.log("Cargando bebidas...");
        
        fetch('api/menu.php?categoria=bebida')
            .then(response => response.json())
            .then(data => {
                console.log("Datos de bebidas recibidos:", data);
                mostrarItemsMenuVisual(data.items, tabBebidas);
            })
            .catch(error => {
                console.error('Error al cargar bebidas:', error);
                tabBebidas.innerHTML = '<div class="error-message">Error al cargar bebidas: ' + error.message + '</div>';
            });
    }
    
    /**
     * Carga y muestra los postres
     */
    function cargarPostres() {
        console.log("Cargando postres...");
        
        fetch('api/menu.php?categoria=postre')
            .then(response => response.json())
            .then(data => {
                console.log("Datos de postres recibidos:", data);
                mostrarItemsMenuVisual(data.items, tabPostres);
            })
            .catch(error => {
                console.error('Error al cargar postres:', error);
                tabPostres.innerHTML = '<div class="error-message">Error al cargar postres: ' + error.message + '</div>';
            });
    }
    
    /**
     * Muestra los items del menú con un estilo visual similar a la página web
     */
    function mostrarItemsMenuVisual(items, container) {
        console.log("Mostrando items en el contenedor:", container.id, items);
        
        // Limpiar contenedor
        container.innerHTML = '';
        
        if (!items || items.length === 0) {
            container.innerHTML = '<div class="empty-message">No hay elementos para mostrar</div>';
            return;
        }
        
        // Crear el grid de productos
        const grid = document.createElement('div');
        grid.className = 'menu-grid';
        
        // Agregar cada item al grid
        items.forEach(item => {
            const card = document.createElement('div');
            card.className = 'menu-card';
            card.dataset.id = item.id;
            
            // Formatear el precio
            const precioFormateado = parseInt(item.precio).toLocaleString('es-CO') + ' COP';
            
            // Determinar la ruta de la imagen
            const rutaImagen = item.categoria === 'bebida' ? 
                               `img-bebidas/${item.imagen}` : 
                               `img/${item.imagen}`;
            
            // Construir HTML de la tarjeta
            card.innerHTML = `
                <div class="menu-card-image">
                    <img src="${rutaImagen}" alt="${item.nombre}" onerror="this.src='img/placeholder.png'">
                    ${!parseInt(item.disponible) ? '<div class="no-disponible">No Disponible</div>' : ''}
                </div>
                <div class="menu-card-content">
                    <h3>${item.nombre}</h3>
                    <div class="menu-card-description">
                        <p>${item.descripcion || 'Sin descripción'}</p>
                    </div>
                    <div class="menu-card-footer">
                        <p class="precio">${precioFormateado}</p>
                        <div class="menu-card-actions">
                            <button class="btn btn-primary btn-sm editar-item" data-id="${item.id}">
                                <i class="fa-solid fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-danger btn-sm eliminar-item" data-id="${item.id}">
                                <i class="fa-solid fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Agregar al grid
            grid.appendChild(card);
        });
        
        // Agregar el grid al contenedor
        container.appendChild(grid);
        
        // Agregar event listeners para botones de edición y eliminación
        container.querySelectorAll('.editar-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                console.log("Editar ítem:", itemId);
                editarItem(itemId);
            });
        });
        
        container.querySelectorAll('.eliminar-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                console.log("Eliminar ítem:", itemId);
                confirmarEliminarItem(itemId);
            });
        });
    }
    
    /**
     * Abre el modal para editar un ítem
     */
    function editarItem(itemId) {
        console.log("Obteniendo datos del ítem:", itemId);
        
        fetch(`api/menu_item.php?id=${itemId}`)
            .then(response => response.json())
            .then(data => {
                console.log("Datos del ítem recibidos:", data);
                
                if (data.status === 'success') {
                    const item = data.item;
                    
                    // Actualizar formulario
                    document.getElementById('item-id').value = item.id;
                    document.getElementById('item-nombre').value = item.nombre;
                    document.getElementById('item-categoria').value = item.categoria;
                    document.getElementById('item-descripcion').value = item.descripcion || '';
                    document.getElementById('item-precio').value = item.precio;
                    document.getElementById('item-disponible').checked = item.disponible === '1';
                    
                    // Vista previa de imagen
                    const preview = document.getElementById('preview-imagen');
                    const rutaImagen = item.categoria === 'bebida' ? 
                                     `img-bebidas/${item.imagen}` : 
                                     `img/${item.imagen}`;
                    preview.innerHTML = item.imagen ? `<img src="${rutaImagen}" alt="${item.nombre}">` : '';
                    
                    // Actualizar título
                    document.getElementById('titulo-modal-item').textContent = 'Editar Ítem del Menú';
                    
                    // Mostrar modal
                    document.getElementById('modal-item-menu').style.display = 'block';
                } else {
                    mostrarNotificacion(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error al cargar ítem del menú:', error);
                mostrarNotificacion('Error al cargar ítem del menú: ' + error.message, 'error');
            });
    }
    
    /**
     * Muestra el modal de confirmación para eliminar un ítem
     */
    function confirmarEliminarItem(itemId) {
        const mensaje = '¿Estás seguro de que deseas eliminar este ítem? Esta acción no se puede deshacer.';
        
        document.getElementById('mensaje-confirmacion').textContent = mensaje;
        
        // Configurar callback para confirmación
        document.getElementById('confirmar-si').onclick = function() {
            eliminarItem(itemId);
            document.getElementById('modal-confirmacion').style.display = 'none';
        };
        
        document.getElementById('confirmar-no').onclick = function() {
            document.getElementById('modal-confirmacion').style.display = 'none';
        };
        
        // Mostrar modal
        document.getElementById('modal-confirmacion').style.display = 'block';
    }
    
    /**
     * Elimina un ítem del menú
     */
    function eliminarItem(itemId) {
        console.log("Eliminando ítem:", itemId);
        
        fetch(`api/menu_delete.php?id=${itemId}`, { method: 'DELETE' })
            .then(response => response.json())
            .then(data => {
                console.log("Respuesta al eliminar:", data);
                
                if (data.status === 'success') {
                    mostrarNotificacion(data.message, 'success');
                    
                    // Recargar lista según la categoría activa
                    const tabActivo = document.querySelector('.tab.active').getAttribute('data-tab');
                    switch(tabActivo) {
                        case 'platos':
                            cargarPlatos();
                            break;
                        case 'bebidas':
                            cargarBebidas();
                            break;
                        case 'postres':
                            cargarPostres();
                            break;
                    }
                } else {
                    mostrarNotificacion(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error al eliminar ítem del menú:', error);
                mostrarNotificacion('Error al eliminar ítem del menú: ' + error.message, 'error');
            });
    }
    
    /**
     * Muestra una notificación
     */
    function mostrarNotificacion(mensaje, tipo = 'success') {
        console.log(`Notificación (${tipo}):`, mensaje);
        
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
    
    // Manejar formulario de ítem del menú
    const formItemMenu = document.getElementById('form-item-menu');
    if (formItemMenu) {
        formItemMenu.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log("Formulario de ítem enviado");
            
            // Crear objeto FormData para enviar los datos, incluyendo la imagen
            const formData = new FormData(this);
            
            // Obtener el ID del ítem (si existe)
            const itemId = formData.get('id');
            console.log("ID de ítem a guardar:", itemId);
            
            // URL para crear o actualizar
            const url = itemId ? `api/menu_update.php?id=${itemId}` : 'api/menu_create.php';
            console.log("URL para guardar:", url);
            
            // Verificar disponibilidad
            formData.set('disponible', document.getElementById('item-disponible').checked ? '1' : '0');
            
            // Imprimir todos los datos del formulario para depuración
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            // Desactivar botón de envío
            const submitBtn = formItemMenu.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
            submitBtn.disabled = true;
            
            // Enviar datos
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log("Respuesta del servidor:", response);
                return response.json();
            })
            .then(data => {
                console.log("Datos recibidos:", data);
                
                if (data.status === 'success') {
                    mostrarNotificacion(data.message, 'success');
                    
                    // Cerrar modal
                    document.getElementById('modal-item-menu').style.display = 'none';
                    
                    // Actualizar lista según la categoría seleccionada
                    const tabActivo = document.querySelector('.tab.active').getAttribute('data-tab');
                    switch(tabActivo) {
                        case 'platos':
                            cargarPlatos();
                            break;
                        case 'bebidas':
                            cargarBebidas();
                            break;
                        case 'postres':
                            cargarPostres();
                            break;
                    }
                } else {
                    mostrarNotificacion(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error al guardar ítem:', error);
                mostrarNotificacion('Error al procesar la solicitud: ' + error.message, 'error');
            })
            .finally(() => {
                // Restaurar botón
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // Cancelar formulario
    document.getElementById('cancelar-item').addEventListener('click', function() {
        document.getElementById('modal-item-menu').style.display = 'none';
    });
});