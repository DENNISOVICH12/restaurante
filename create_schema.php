<?php
/**
 * create_schema.php
 * Script para crear el esquema completo de la base de datos en PostgreSQL
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir el archivo de conexión
require_once 'database.php';

echo "<h1>Creación de Esquema PostgreSQL para Restaurante</h1>";

try {
    // Comenzar transacción
    $conexion->pdo->beginTransaction();
    
    echo "<h2>Creando tablas...</h2>";
    
    // 1. Tabla usuarios
    echo "<p>Creando tabla 'usuarios'...</p>";
    $sql = "CREATE TABLE IF NOT EXISTS usuarios (
        id SERIAL PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        correo VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        rol VARCHAR(50) NOT NULL CHECK (rol IN ('admin', 'mesero', 'cocinero', 'empleado')),
        activo BOOLEAN DEFAULT TRUE,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ultimo_acceso TIMESTAMP
    )";
    $conexion->pdo->exec($sql);
    echo "<p>✅ Tabla 'usuarios' creada</p>";
    
    // 2. Tabla clientes
    echo "<p>Creando tabla 'clientes'...</p>";
    $sql = "CREATE TABLE IF NOT EXISTS clientes (
        id SERIAL PRIMARY KEY,
        nombre_cliente VARCHAR(100) NOT NULL,
        telefono VARCHAR(20),
        direccion TEXT,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conexion->pdo->exec($sql);
    
    // Crear índice único en teléfono
    $conexion->pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_clientes_telefono ON clientes(telefono) WHERE telefono IS NOT NULL");
    echo "<p>✅ Tabla 'clientes' creada</p>";
    
    // 3. Tabla menu_items
    echo "<p>Creando tabla 'menu_items'...</p>";
    $sql = "CREATE TABLE IF NOT EXISTS menu_items (
        id SERIAL PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        precio DECIMAL(10,2) NOT NULL,
        imagen VARCHAR(255),
        categoria VARCHAR(50) NOT NULL CHECK (categoria IN ('plato', 'bebida', 'postre')),
        disponible BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conexion->pdo->exec($sql);
    echo "<p>✅ Tabla 'menu_items' creada</p>";
    
    // 4. Tabla pedidos
    echo "<p>Creando tabla 'pedidos'...</p>";
    $sql = "CREATE TABLE IF NOT EXISTS pedidos (
        id SERIAL PRIMARY KEY,
        id_cliente INTEGER REFERENCES clientes(id),
        nombre_cliente VARCHAR(100),
        telefono VARCHAR(20),
        direccion TEXT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        estado VARCHAR(50) DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'en_preparacion', 'listo', 'en_entrega', 'entregado', 'cancelado'))
    )";
    $conexion->pdo->exec($sql);
    
    // Crear índices
    $conexion->pdo->exec("CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos(estado)");
    $conexion->pdo->exec("CREATE INDEX IF NOT EXISTS idx_pedidos_fecha ON pedidos(fecha)");
    echo "<p>✅ Tabla 'pedidos' creada</p>";
    
    // 5. Tabla detalle_pedido
    echo "<p>Creando tabla 'detalle_pedido'...</p>";
    $sql = "CREATE TABLE IF NOT EXISTS detalle_pedido (
        id SERIAL PRIMARY KEY,
        id_pedido INTEGER REFERENCES pedidos(id) ON DELETE CASCADE,
        nombre_producto VARCHAR(100) NOT NULL,
        categoria VARCHAR(50),
        precio DECIMAL(10,2) NOT NULL,
        cantidad INTEGER NOT NULL DEFAULT 1,
        descripcion TEXT
    )";
    $conexion->pdo->exec($sql);
    
    // Crear índice
    $conexion->pdo->exec("CREATE INDEX IF NOT EXISTS idx_detalle_pedido ON detalle_pedido(id_pedido)");
    echo "<p>✅ Tabla 'detalle_pedido' creada</p>";
    
    // 6. Tabla log_acciones (opcional)
    echo "<p>Creando tabla 'log_acciones'...</p>";
    $sql = "CREATE TABLE IF NOT EXISTS log_acciones (
        id SERIAL PRIMARY KEY,
        usuario_id INTEGER REFERENCES usuarios(id),
        accion VARCHAR(100),
        detalles TEXT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conexion->pdo->exec($sql);
    echo "<p>✅ Tabla 'log_acciones' creada</p>";
    
    // Crear trigger para actualizar fecha_actualizacion en menu_items
    echo "<p>Creando trigger para actualización automática...</p>";
    $sql = "
    CREATE OR REPLACE FUNCTION update_fecha_actualizacion()
    RETURNS TRIGGER AS $$
    BEGIN
        NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
        RETURN NEW;
    END;
    $$ LANGUAGE plpgsql;
    
    DROP TRIGGER IF EXISTS update_menu_items_fecha ON menu_items;
    
    CREATE TRIGGER update_menu_items_fecha 
    BEFORE UPDATE ON menu_items 
    FOR EACH ROW 
    EXECUTE FUNCTION update_fecha_actualizacion();
    ";
    $conexion->pdo->exec($sql);
    echo "<p>✅ Trigger creado</p>";
    
    // Confirmar transacción
    $conexion->pdo->commit();
    
    echo "<h2>✅ Esquema creado exitosamente</h2>";
    
    // Crear usuario administrador por defecto
    echo "<h2>Creando usuario administrador por defecto...</h2>";
    
    // Verificar si ya existe
    $stmt = $conexion->pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute(['admin@restaurante.com']);
    
    if ($stmt->rowCount() == 0) {
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conexion->pdo->prepare("
            INSERT INTO usuarios (nombre, correo, password, rol) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute(['Administrador', 'admin@restaurante.com', $password_hash, 'admin']);
        echo "<p>✅ Usuario administrador creado:</p>";
        echo "<ul>";
        echo "<li>Correo: admin@restaurante.com</li>";
        echo "<li>Contraseña: admin123</li>";
        echo "</ul>";
    } else {
        echo "<p>ℹ️ El usuario administrador ya existe</p>";
    }
    
    // Mostrar resumen
    echo "<h2>Resumen de tablas creadas:</h2>";
    $tables = $conexion->pdo->query("
        SELECT tablename, 
               (SELECT COUNT(*) FROM pg_catalog.pg_indexes WHERE tablename = t.tablename) as indices
        FROM pg_catalog.pg_tables t
        WHERE schemaname = 'public'
        ORDER BY tablename
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Tabla</th><th>Índices</th></tr>";
    foreach ($tables as $table) {
        echo "<tr>";
        echo "<td style='padding: 5px;'>{$table['tablename']}</td>";
        echo "<td style='padding: 5px; text-align: center;'>{$table['indices']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<p><a href='import_menu_data.php'>Importar datos del menú</a> | ";
    echo "<p><a href='test_postgresql_connection.php'>Verificar conexión</a> | ";
    echo "<a href='login.php'>Ir al login</a></p>";
    
} catch (Exception $e) {
    $conexion->pdo->rollBack();
    echo "<h2>❌ Error al crear el esquema</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>