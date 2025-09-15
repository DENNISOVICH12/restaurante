<?php
// Incluir el archivo de conexión a la base de datos
if (file_exists('database.php')) {
    require_once 'database.php';
}

// Verificar si el usuario está logueado
function is_logged_in() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Verificar si el usuario tiene rol de administrador
function is_admin() {
    return is_logged_in() && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

// Verificar si el usuario tiene rol de cocinero
function is_cocinero() {
    return is_logged_in() && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'cocinero';
}

// Verificar si el usuario tiene rol de mesero
function is_mesero() {
    return is_logged_in() && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'mesero';
}

// Verificar si el usuario tiene rol de empleado genérico
function is_empleado() {
    return is_logged_in() && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'empleado';
}

// Redirigir si no está autenticado
function require_login() {
    if (!is_logged_in()) {
        // Guardar la URL actual para redirigir después del login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit;
    }
}

// Redirigir si no es administrador
function require_admin() {
    require_login();
    if (!is_admin()) {
        // No tiene permisos de administrador
        header("Location: dashboard.php?error=no_permission");
        exit;
    }
}

// Redirigir si no es cocinero
function require_cocinero() {
    require_login();
    if (!is_cocinero() && !is_admin()) { // Los administradores también pueden acceder
        header("Location: dashboard.php?error=no_permission");
        exit;
    }
}

// Redirigir si no es mesero
function require_mesero() {
    require_login();
    if (!is_mesero() && !is_admin()) { // Los administradores también pueden acceder
        header("Location: dashboard.php?error=no_permission");
        exit;
    }
}

// Cerrar sesión
function logout() {
    // Eliminar todas las variables de sesión
    $_SESSION = array();

    // Si se desea destruir la sesión completamente, borrar también la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finalmente, destruir la sesión
    session_destroy();
}
?>