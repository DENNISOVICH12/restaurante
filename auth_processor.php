<?php
// Incluir el archivo de conexión a la base de datos
require_once 'database.php';

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Determinar si es una solicitud de registro o inicio de sesión
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Procesar registro
        if ($action == 'register') {
            // Obtener datos del formulario
            $nombre = trim($_POST['nombre']);
            $correo = trim($_POST['correo']);
            $password = $_POST['password'];
            
            // Verificar y validar el rol
            $rolValido = false;
            $rolPermitido = false;
            
            if (isset($_POST['rol'])) {
                $rol = $_POST['rol'];
                
                // Validar que el rol sea uno de los valores permitidos
                if (in_array($rol, ['mesero', 'cocinero', 'admin', 'empleado'])) {
                    $rolValido = true;
                    
                    // Verificar permisos para asignar rol de administrador
                    if ($rol === 'admin' && (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin')) {
                        $rol = 'mesero'; // Default a mesero si no es admin intentando crear admin
                    } else {
                        $rolPermitido = true;
                    }
                } else {
                    $rol = 'mesero'; // Default a mesero si el rol no es válido
                }
            } else {
                $rol = 'mesero'; // Default a mesero si no se especifica
            }

            // Validaciones básicas
            if (empty($nombre) || empty($correo) || empty($password)) {
                $response = [
                    'status' => 'error',
                    'message' => 'Todos los campos son obligatorios'
                ];
            } else {
                // Validar correo electrónico
                if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Formato de correo electrónico inválido'
                    ];
                } else {
                    // Verificar si el correo ya existe
                    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
                    $stmt->bind_param("s", $correo);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $response = [
                            'status' => 'error',
                            'message' => 'Este correo electrónico ya está registrado'
                        ];
                    } else {
                        // Hashear la contraseña
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        // Insertar nuevo usuario
                        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("ssss", $nombre, $correo, $hashed_password, $rol);

                        if ($stmt->execute()) {
                            $response = [
                                'status' => 'success',
                                'message' => 'Usuario registrado exitosamente'
                            ];
                            
                            // Si es un administrador creando usuarios, ofrecer crear más
                            if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
                                $response['redirect'] = 'usuarios.php'; // O donde gestiones usuarios
                            } else {
                                $response['redirect'] = 'login.php'; // Redirigir a login para usuarios normales
                            }
                        } else {
                            $response = [
                                'status' => 'error',
                                'message' => 'Error al registrar el usuario: ' . $stmt->error
                            ];
                        }
                    }
                }
            }
        }
        // Procesar inicio de sesión 
        else if ($action == 'login') {
            // Obtener datos del formulario
            $correo = trim($_POST['correo']);
            $password = $_POST['password'];

            // Validaciones básicas
            if (empty($correo) || empty($password)) {
                $response = [
                    'status' => 'error',
                    'message' => 'Todos los campos son obligatorios'
                ];
            } else {
                // Buscar usuario por correo
                $stmt = $conexion->prepare("SELECT id, nombre, correo, password, rol FROM usuarios WHERE correo = ? AND activo = TRUE");
                $stmt->bind_param("s", $correo);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $usuario = $result->fetch_assoc();
                    
                    // Verificar contraseña
                    if (password_verify($password, $usuario['password'])) {
                        // Actualizar último acceso
                        $update_stmt = $conexion->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                        $update_stmt->bind_param("i", $usuario['id']);
                        $update_stmt->execute();

                        // Crear sesión
                        $_SESSION['usuario_id'] = $usuario['id'];
                        $_SESSION['usuario_nombre'] = $usuario['nombre'];
                        $_SESSION['usuario_correo'] = $usuario['correo'];
                        $_SESSION['usuario_rol'] = $usuario['rol'];
                        $_SESSION['loggedin'] = true;

                        // Determinar redirección según el rol
                        $redirect = 'dashboard.php';
                        if ($usuario['rol'] === 'cocinero') {
                            $redirect = 'cocina.php';
                        } elseif ($usuario['rol'] === 'mesero') {
                            $redirect = 'meseros.php';
                        }

                        $response = [
                            'status' => 'success',
                            'message' => 'Inicio de sesión exitoso',
                            'redirect' => $redirect
                        ];
                    } else {
                        $response = [
                            'status' => 'error',
                            'message' => 'Contraseña incorrecta'
                        ];
                    }
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Usuario no encontrado o inactivo'
                    ];
                }
            }
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Acción no válida'
            ];
        }

        // Devolver respuesta JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}