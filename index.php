<?php
// index.php — versión corregida para PostgreSQL
require_once __DIR__ . '/database.php';

/**
 * Traemos los platos desde Postgres.
 * NOTA: en Postgres los booleanos son TRUE/FALSE (no 1/0).
 * Usamos helpers parametrizados para evitar inyección.
 */
$platos = db_fetch_all(
    "SELECT id, nombre, descripcion, precio, imagen
     FROM menu_items
     WHERE categoria = $1 AND disponible = $2
     ORDER BY id ASC",
    ['plato', true]
);

// Separar “entradas” vs “platos principales” (regla simple: 3 primeros como entradas)
$entradas = array_slice($platos, 0, 3);
$platos_principales = array_slice($platos, 3);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Restaurante</title>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>

    <header class="header">
        <div class="menu container">
            <a href="#" class="logo">Restaurante</a>
            <input type="checkbox" id="menu"/>
            <label for="menu">
                <img src="img/menu.png" class="menu-icono" alt="menu">
            </label>

            <nav class="navbar">
                <ul>
                    <li><a href="index.php">PLATOS</a></li>
                    <li><a href="bebidas.php">BEBIDAS</a></li>
                    <li><a href="#">COCTELERÍA</a></li>
                    <li><a href="#">POSTRES</a></li>
                </ul>
            </nav>

            <div>
                <ul>
                    <li class="submenu">
                        <img src="img/carrito2.png" id="img-carrito" alt="carrito">
                        <div id="carrito">
                            <table id="lista-carrito">
                                <thead>
                                    <tr>
                                        <th>Imagen</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <a href="#" id="vaciar-carrito" class="btn-3">Vaciar carrito</a>
                            <a href="#" id="btn-confirmar" class="btn-1">Confirmar pedido</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="header-content container">
            <div class="header-txt">
                <h1>Bienvenido</h1>
                <p>Elige tus platos favoritos</p>
                <a href="#lista-1" class="btn-1">Ver menú</a>
            </div>
            <div class="header-img">
                <img src="img/hamburguesa.png" alt="">
            </div>
        </div>
    </header>

    <section class="oferts container">
        <div class="ofert-1 b1">
            <div class="ofert-txt">
                <h3>Entradas</h3>
                <p>Las sugerencias del chef</p>
            </div>
            <div class="ofert-img">
                <img src="img/o1.png" alt="">
            </div>
        </div>

        <?php foreach ($entradas as $entrada): ?>
        <div class="ofert-1 b2">
            <div class="ofert-img">
                <img src="img/<?php echo htmlspecialchars($entrada['imagen'] ?? ''); ?>"
                     alt="<?php echo htmlspecialchars($entrada['nombre']); ?>">
            </div>
            <div class="ofert-txt">
                <h3><?php echo htmlspecialchars($entrada['nombre']); ?></h3>
                <p><?php echo htmlspecialchars($entrada['descripcion']); ?></p>
                <p class="precio"><?php echo number_format((float)$entrada['precio'], 0, ',', '.'); ?> COP</p>
                <a href="#" class="agregar-carrito btn-2" data-id="<?php echo (int)$entrada['id']; ?>">agregar al carrito</a>
            </div>
        </div>
        <?php endforeach; ?>
    </section>

    <main class="products container" id="lista-1">
        <h2>Platos principales</h2>
        <div class="product-content">
            <?php foreach ($platos_principales as $plato): ?>
            <div class="product" data-categoria="Plato">
                <img src="img/<?php echo htmlspecialchars($plato['imagen'] ?? ''); ?>"
                     alt="<?php echo htmlspecialchars($plato['nombre']); ?>">
                <div class="product-txt">
                    <h3><?php echo htmlspecialchars($plato['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($plato['descripcion']); ?></p>
                    <p class="precio"><?php echo number_format((float)$plato['precio'], 0, ',', '.'); ?> COP</p>
                    <a href="#" class="agregar-carrito btn-2" data-id="<?php echo (int)$plato['id']; ?>">agregar al carrito</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Modal datos del cliente -->
    <div id="modal-cliente" class="modal">
        <div class="modal-content">
            <span class="close" id="cerrar-modal">&times;</span>
            <h2>Datos del cliente</h2>
            <form id="form-cliente">
                <div class="form-group">
                    <label for="nombre">Nombre completo:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" required>
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección (opcional):</label>
                    <input type="text" id="direccion" name="direccion">
                </div>
                <button type="submit" class="btn-1">Confirmar Pedido</button>
            </form>
        </div>
    </div>

    <script src="js/enviar.js?v=1"></script>
</body>
</html>
