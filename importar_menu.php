<?php
/**
 * import_menu_data.php
 * Script para importar platos y bebidas al sistema PostgreSQL
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la conexión a la base de datos
require_once 'database.php';

echo "<h1>Importación de Datos del Menú a PostgreSQL</h1>";

// Array para almacenar los platos
$platos = [
    [
        'nombre' => 'Empanadas con ají',
        'descripcion' => 'Tradicionales empanadas colombianas acompañadas de ají casero.',
        'precio' => 15000,
        'imagen' => 'entrada1.png',
        'categoria' => 'plato'
    ],
    [
        'nombre' => 'Tostada española',
        'descripcion' => 'Tostada al estilo español con ingredientes frescos y aceite de oliva.',
        'precio' => 12000,
        'imagen' => 'entrada3.png',
        'categoria' => 'plato'
    ],
    [
        'nombre' => 'Sushi variado',
        'descripcion' => 'Selección de sushi fresco con pescados del día.',
        'precio' => 25000,
        'imagen' => 'entrada4.png',
        'categoria' => 'plato'
    ],
    [
        'nombre' => 'Churrasco',
        'descripcion' => 'Corte de res de la parte trasera, jugoso y tierno, acompañado con papas a la francesa y vegetales salteados.',
        'precio' => 45000,
        'imagen' => 'PLATO1.png',
        'categoria' => 'plato'
    ],
    [
        'nombre' => 'Ensalada marina',
        'descripcion' => 'Fresca ensalada con mariscos, lechuga, cortes de naranja y tomate, acompañada de vinagreta agridulce.',
        'precio' => 30000,
        'imagen' => 'PLATO2.png',
        'categoria' => 'plato'
    ],
    [
        'nombre' => 'Filet Mignon',
        'descripcion' => 'Corte premium de res extremadamente tierno, proveniente del solomillo. Servido con salsa de champiñones y zanahoria glaseada.',
        'precio' => 40000,
        'imagen' => 'PLATO3.png',
        'categoria' => 'plato'
    ],
    [
        'nombre' => 'Pulpo a la parrilla',
        'descripcion' => 'Pulpo tierno a la parrilla acompañado de caviar y salsa marinera especial de la casa.',
        'precio' => 80000,
        'imagen' => 'PLATO4.png',
        'categoria' => 'plato'
    ]
];

// Array para almacenar las bebidas
$bebidas = [
    [
        'nombre' => 'Té helado',
        'descripcion' => 'Refrescante té helado con limón y hierbabuena.',
        'precio' => 8000,
        'imagen' => 'te.jpg',
        'categoria' => 'bebida'
    ],
    [
        'nombre' => 'Limonada natural',
        'descripcion' => 'Limonada preparada con limones frescos y un toque de hierbabuena.',
        'precio' => 5000,
        'imagen' => 'limonada1.jpg',
        'categoria' => 'bebida'
    ],
    [
        'nombre' => 'Agua mineral',
        'descripcion' => 'Agua mineral con gas o sin gas.',
        'precio' => 2500,
        'imagen' => 'agua_mineral.jpg',
        'categoria' => 'bebida'
    ],
    [
        'nombre' => 'Coca Cola',
        'descripcion' => 'Bebida gaseosa Coca Cola original.',
        'precio' => 4500,
        'imagen' => 'coca-cola.png',
        'categoria' => 'bebida'
    ],
    [
        'nombre' => 'Sprite',
        'descripcion' => 'Gaseosa refrescante con sabor a limón y lima.',
        'precio' => 3500,
        'imagen' => 'Sprite.webp',
        'categoria' => 'bebida'
    ],
    [
        'nombre' => 'Pepsi',
        'descripcion' => 'Gaseosa Pepsi refrescante sabor cola.',
        'precio' => 3500,
        'imagen' => 'pepsi.png',
        'categoria' => 'bebida'
    ],
    [
        'nombre' => 'Piña colada',
        'descripcion' => 'Bebida tropical con piña, crema de coco y hielo.',
        'precio' => 8000,
        'imagen' => '125707_large.jpg',
        'categoria' => 'bebida'
    ],
    [
        'nombre' => 'Limonada cerezada',
        'descripcion' => 'Refrescante limonada con cerezas dulces.',
        'precio' => 8000,
        'imagen' => 'limonada_cerezada.png',
        'categoria' => 'bebida'
    ],
    [
        'nombre' => 'Cóctel de frutas',
        'descripcion' => 'Mezcla refrescante de frutas tropicales con un toque de licor.',
        'precio' => 20000,
        'imagen' => 'coctel.png',
        'categoria' => 'bebida'
    ]
];

// Array de postres
$postres = [
    [
        'nombre' => 'Tiramisú',
        'descripcion' => 'Clásico postre italiano con café, mascarpone y cacao.',
        'precio' => 12000,
        'imagen' => 'tiramisu.jpg',
        'categoria' => 'postre'
    ],
    [
        'nombre' => 'Flan de caramelo',
        'descripcion' => 'Suave flan casero con caramelo dorado.',
        'precio' => 8000,
        'imagen' => 'flan.jpg',
        'categoria' => 'postre'
    ],
    [
        'nombre' => 'Helado artesanal',
        'descripcion' => 'Tres bolas de helado artesanal, sabores a elección.',
        'precio' => 10000,
        'imagen' => 'helado.jpg',
        'categoria' => 'postre'
    ]
];

// Combinar todos los arrays
$menu_items = array_merge($platos, $bebidas, $postres);

try {
    // Iniciar transacción
    $conexion->pdo->beginTransaction();
    
    echo "<h2>Importando ítems del menú...</h2>";
    
    // Preparar la consulta
    $stmt = $conexion->pdo->prepare("
        INSERT INTO menu_items (nombre, descripcion, precio, imagen, categoria, disponible) 
        VALUES (:nombre, :descripcion, :precio, :imagen, :categoria, :disponible)
        ON CONFLICT DO NOTHING
    ");
    
    $count_new = 0;
    $count_existing = 0;
    
    foreach ($menu_items as $item) {
        // Verificar si el ítem ya existe
        $check = $conexion->pdo->prepare("SELECT id FROM menu_items WHERE nombre = :nombre AND categoria = :categoria");
        $check->execute([
            ':nombre' => $item['nombre'],
            ':categoria' => $item['categoria']
        ]);
        
        if ($check->rowCount() > 0) {
            echo "<p>⚠️ '{$item['nombre']}' ya existe en la base de datos.</p>";
            $count_existing++;
            continue;
        }
        
        // Insertar el nuevo ítem
        $result = $stmt->execute([
            ':nombre' => $item['nombre'],
            ':descripcion' => $item['descripcion'],
            ':precio' => $item['precio'],
            ':imagen' => $item['imagen'],
            ':categoria' => $item['categoria'],
            ':disponible' => true
        ]);
        
        if ($result) {
            echo "<p>✅ '{$item['nombre']}' añadido correctamente.</p>";
            $count_new++;
        } else {
            echo "<p>❌ Error al añadir '{$item['nombre']}'</p>";
        }
    }
    
    // Confirmar transacción
    $conexion->pdo->commit();
    
    echo "<h2>Proceso completado</h2>";
    echo "<ul>";
    echo "<li>✅ Nuevos ítems añadidos: $count_new</li>";
    echo "<li>⚠️ Ítems ya existentes: $count_existing</li>";
    echo "<li>📊 Total procesados: " . count($menu_items) . "</li>";
    echo "</ul>";
    
    // Mostrar resumen por categoría
    echo "<h3>Resumen por categoría:</h3>";
    $resumen = $conexion->pdo->query("
        SELECT categoria, COUNT(*) as cantidad, 
               ROUND(AVG(precio)::numeric, 2) as precio_promedio
        FROM menu_items 
        GROUP BY categoria 
        ORDER BY categoria
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Categoría</th><th>Cantidad</th><th>Precio Promedio</th></tr>";
    foreach ($resumen as $cat) {
        echo "<tr>";
        echo "<td style='padding: 5px;'>" . ucfirst($cat['categoria']) . "</td>";
        echo "<td style='padding: 5px; text-align: center;'>{$cat['cantidad']}</td>";
        echo "<td style='padding: 5px; text-align: right;'>$" . number_format($cat['precio_promedio'], 0, ',', '.') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Crear algunos pedidos de prueba
    echo "<h3>¿Desea crear datos de prueba?</h3>";
    echo "<p><a href='create_test_data.php'>Crear pedidos de prueba</a></p>";
    
} catch (Exception $e) {
    $conexion->pdo->rollBack();
    echo "<h2>❌ Error durante la importación</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='dashboard.php'>Ir al panel de administración</a> | ";
echo "<a href='index.php'>Ver sitio web</a> | ";
echo "<a href='test_postgresql_connection.php'>Verificar conexión</a></p>";
?>