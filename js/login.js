/**
 * JavaScript para manejar el formulario de inicio de sesión y registro
 */
document.addEventListener('DOMContentLoaded', function() {
    let singUp = document.getElementById("singUp");
    let singIn = document.getElementById("singIn");
    let nameInput = document.getElementById("nameInput");
    let title = document.getElementById("title");
    let authForm = document.getElementById("auth-form");
    let actionField = document.getElementById("action");
    let alertMessage = document.getElementById("alert-message");

    // Cambiar a modo login
    singIn.onclick = function() {
        nameInput.style.maxHeight = "0";
        title.innerHTML = "Iniciar Sesión";
        singUp.classList.add("disable");
        singIn.classList.remove("disable");
        actionField.value = "login";
        
        // Enviar formulario
        submitForm();
    }

    // Cambiar a modo registro
    singUp.onclick = function() {
        nameInput.style.maxHeight = "60px";
        title.innerHTML = "Registro";
        singUp.classList.remove("disable");
        singIn.classList.add("disable");
        actionField.value = "register";
        
        // Enviar formulario
        submitForm();
    }

    // Función para enviar el formulario
    function submitForm() {
        // Validación básica
        const correo = document.getElementById('correo').value;
        const password = document.getElementById('password').value;
        
        if (!correo || !password) {
            showAlert('error', 'Por favor completa todos los campos requeridos');
            return;
        }
        
        // Si es registro, validar el nombre
        if (actionField.value === 'register') {
            const nombre = document.getElementById('nombre').value;
            if (!nombre) {
                showAlert('error', 'Por favor ingresa tu nombre');
                return;
            }
        }
        
        // Recopilar los datos del formulario
        const formData = new FormData(authForm);
        
        // Enviar solicitud AJAX
        fetch('auth_processor.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showAlert('success', data.message);
                
                // Si hay redirección, redirigir después de un breve retraso
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                }
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Ha ocurrido un error en la comunicación con el servidor');
        });
    }

    // Función para mostrar mensajes de alerta
    function showAlert(type, message) {
        alertMessage.textContent = message;
        alertMessage.className = 'alert ' + type;
        alertMessage.style.display = 'block';
        
        // Ocultar el mensaje después de 5 segundos
        setTimeout(() => {
            alertMessage.style.display = 'none';
        }, 5000);
    }

    // Evento para prevenir el envío normal del formulario
    authForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Determinar la acción basada en qué botón está activo
        if (!singUp.classList.contains('disable')) {
            actionField.value = 'register';
        } else {
            actionField.value = 'login';
        }
        
        submitForm();
    });

    // Manejar el enlace de contraseña olvidada
    document.getElementById('forgot-password').addEventListener('click', function(e) {
        e.preventDefault();
        alert('Funcionalidad de recuperación de contraseña no implementada aún.');
    });
});