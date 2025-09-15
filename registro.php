<?php
// Incluir el archivo de conexión a la base de datos y funciones de autenticación
require_once 'database.php';
require_once 'auth_functions.php';

// Si hay una sesión activa, verificar si es administrador
$is_admin = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $is_admin = ($_SESSION['usuario_rol'] === 'admin');
    
    // Si no es administrador y ya está logueado, redirigir al dashboard
    if (!$is_admin) {
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario | Restaurante</title>
    <link rel="stylesheet" href="css/registro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Estilos adicionales para el selector de rol */
        .input-field select {
            width: 100%;
            padding: 10px;
            border: 0;
            outline: 0;
            background: transparent;
            font-size: 16px;
        }
        
        .input-field i.fa-user-tag {
            margin-right: 10px;
        }
        
        .rol-description {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
            padding-left: 30px;
        }
        
        .rol-description ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        
        .rol-description li {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-content">
            <h1 id="title">Registro de Usuario</h1>
            
            <!-- Mensaje de alerta para mostrar respuestas del servidor -->
            <div id="alert-message" class="alert" style="display: none;"></div>
            
            <form id="auth-form" method="post">
                <div class="input-group">
                    <div class="input-field" id="nameInput">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="nombre" id="nombre" placeholder="Nombre completo" required>
                    </div>
                    <div class="input-field">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="correo" id="correo" placeholder="Correo electrónico" required>
                    </div>
                    <div class="input-field">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Contraseña" required>
                    </div>
                    <div class="input-field">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password_confirm" id="password_confirm" placeholder="Confirmar contraseña" required>
                    </div>
                    <div class="input-field">
                        <i class="fa-solid fa-user-tag"></i>
                        <select name="rol" id="rol" required>
                            <option value="mesero">Mesero</option>
                            <option value="cocinero">Cocinero</option>
                            <?php if ($is_admin): ?>
                            <option value="admin">Administrador</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <!-- Descripción del rol seleccionado -->
                    <div class="rol-description" id="rol-description">
                        <strong>Funciones del mesero:</strong>
                        <ul>
                            <li>Ver pedidos listos para entregar</li>
                            <li>Marcar pedidos como "En entrega" y "Entregado"</li>
                            <li>Ver información de los clientes</li>
                        </ul>
                    </div>
                    
                    <?php if (!$is_admin): ?>
                    <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p>
                    <?php endif; ?>
                </div>
                <div class="btn-field">
                    <button type="submit" id="register-btn">Registrarse</button>
                    <?php if ($is_admin): ?>
                    <button type="button" onclick="window.location.href='dashboard.php'" class="btn-secondary">Volver al Panel</button>
                    <?php endif; ?>
                </div>
                <!-- Campo oculto para determinar la acción -->
                <input type="hidden" name="action" id="action" value="register">
            </form>
            
            <!-- Enlace para volver a la página principal -->
            <div class="back-link">
                <a href="index.php">Volver a la página principal</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const authForm = document.getElementById('auth-form');
            const rolSelect = document.getElementById('rol');
            const rolDescription = document.getElementById('rol-description');
            
            // Descripciones de roles
            const rolDescriptions = {
                'mesero': `
                    <strong>Funciones del mesero:</strong>
                    <ul>
                        <li>Ver pedidos listos para entregar</li>
                        <li>Marcar pedidos como "En entrega" y "Entregado"</li>
                        <li>Ver información de los clientes</li>
                    </ul>
                `,
                'cocinero': `
                    <strong>Funciones del cocinero:</strong>
                    <ul>
                        <li>Ver pedidos pendientes</li>
                        <li>Cambiar estado a "En preparación" y "Listo"</li>
                        <li>Ver detalles de cada pedido</li>
                    </ul>
                `,
                'admin': `
                    <strong>Funciones del administrador:</strong>
                    <ul>
                        <li>Acceso completo al sistema</li>
                        <li>Gestión de menú y usuarios</li>
                        <li>Estadísticas y reportes</li>
                        <li>Configuración del restaurante</li>
                    </ul>
                `
            };
            
            // Actualizar descripción cuando cambie el rol seleccionado
            rolSelect.addEventListener('change', function() {
                const selectedRol = this.value;
                rolDescription.innerHTML = rolDescriptions[selectedRol] || '';
            });
            
            // Evento para enviar el formulario
            authForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validación básica
                const nombre = document.getElementById('nombre').value;
                const correo = document.getElementById('correo').value;
                const password = document.getElementById('password').value;
                const passwordConfirm = document.getElementById('password_confirm').value;
                
                if (!nombre || !correo || !password || !passwordConfirm) {
                    showAlert('error', 'Por favor completa todos los campos');
                    return;
                }
                
                if (password !== passwordConfirm) {
                    showAlert('error', 'Las contraseñas no coinciden');
                    return;
                }
                
                // Validación de formato de correo
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(correo)) {
                    showAlert('error', 'Por favor ingresa un correo electrónico válido');
                    return;
                }
                
                // Validación de contraseña (mínimo 6 caracteres)
                if (password.length < 6) {
                    showAlert('error', 'La contraseña debe tener al menos 6 caracteres');
                    return;
                }
                
                // Recopilar los datos del formulario
                const formData = new FormData(authForm);
                
                // Mostrar mensaje de carga
                const registerBtn = document.getElementById('register-btn');
                const originalBtnText = registerBtn.textContent;
                registerBtn.disabled = true;
                registerBtn.textContent = 'Procesando...';
                
                // Enviar solicitud AJAX
                fetch('auth_processor.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    registerBtn.disabled = false;
                    registerBtn.textContent = originalBtnText;
                    
                    if (data.status === 'success') {
                        showAlert('success', data.message);
                        
                        // Limpiar formulario si es necesario
                        if (!<?php echo $is_admin ? 'true' : 'false'; ?>) {
                            authForm.reset();
                        }
                        
                        // Si hay redirección o es admin, redirigir después de un breve retraso
                        if (data.redirect || <?php echo $is_admin ? 'true' : 'false'; ?>) {
                            setTimeout(() => {
                                window.location.href = data.redirect || 'dashboard.php';
                            }, 1500);
                        }
                    } else {
                        showAlert('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    registerBtn.disabled = false;
                    registerBtn.textContent = originalBtnText;
                    showAlert('error', 'Ha ocurrido un error en la comunicación con el servidor');
                });
            });
            
            // Función para mostrar mensajes de alerta
            function showAlert(type, message) {
                const alertMessage = document.getElementById('alert-message');
                alertMessage.textContent = message;
                alertMessage.className = 'alert ' + type;
                alertMessage.style.display = 'block';
                
                // Desplazarse al principio del formulario para ver el mensaje
                window.scrollTo(0, 0);
                
                // Ocultar el mensaje después de 5 segundos
                setTimeout(() => {
                    alertMessage.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>