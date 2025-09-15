<?php
// Incluir el archivo de funciones de autenticación
require_once 'auth_functions.php';

// Cerrar la sesión
logout();

// Redirigir al inicio de sesión
header("Location: login.php");
exit;
?>