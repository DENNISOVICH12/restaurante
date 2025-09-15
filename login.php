<?php
// Incluir el archivo de conexión a la base de datos y funciones de autenticación
require_once 'database.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Restaurante</title>
    <link rel="stylesheet" href="css/registro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container">
        <div class="form-content">
            <h1 id="title">Iniciar Sesión</h1>
            
            <!-- Mensaje de alerta para mostrar respuestas del servidor -->
            <div id="alert-message" class="alert" style="display: none;"></div>
            
            <form id="auth-form" method="post">
                <div class="input-group">
                    <div class="input-field" id="nameInput" style="max-height: 0;">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="nombre" id="nombre" placeholder="Nombre completo">
                    </div>
                    <div class="input-field">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="correo" id="correo" placeholder="Correo electrónico" required>
                    </div>
                    <div class="input-field">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Contraseña" required>
                    </div>
                    <p>¿Olvidaste tu contraseña? <a href="#" id="forgot-password">Click aquí</a></p>
                </div>
                <div class="btn-field">
                    <button type="button" id="singUp" class="disable">Registro</button>
                    <button type="button" id="singIn">Iniciar Sesión</button>
                </div>
                <!-- Campo oculto para determinar la acción -->
                <input type="hidden" name="action" id="action" value="login">
            </form>
            
            <!-- Enlace para volver a la página principal -->
            <div class="back-link">
                <a href="index.html">Volver a la página principal</a>
            </div>
        </div>
    </div>

    <script src="js/login.js"></script>
</body>
</html>