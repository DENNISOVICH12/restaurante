<?php
// bebidas.php — lista solo bebidas
require_once __DIR__ . '/database.php';

$bebidas = db_fetch_all(
    "SELECT id, nombre, descripcion, precio, imagen
     FROM menu_items
     WHERE categoria = $1 AND disponible = $2
     ORDER BY id ASC",
    ['bebida', true]
);

// Fallback de imagen si en la BD está vacía o no existe el archivo
function bebida_img($fila) {
    $img = $fila['imagen'] ?? '';
    if ($img && file_exists(__DIR__."/img/".$img)) return $img;
    // usa alguna que tengas en /img
    return 'BEBIDA1.png';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bebidas</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
  <div class="menu container">
    <a href="index.php" class="logo">Restaurante</a>
    <input type="checkbox" id="menu"/>
    <label for="menu"><img src="img/menu.png" class="menu-icono" alt="menu"></label>

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
      <h1>Bebidas</h1>
      <p>Refrescos, jugos y más</p>
      <a href="#lista-bebidas" class="btn-1">Ver bebidas</a>
    </div>
    <div class="header-img">
      <img src="img/BEBIDA1.png" alt="">
    </div>
  </div>
</header>

<main class="products container" id="lista-bebidas">
  <h2>Nuestras bebidas</h2>
  <div class="product-content">
    <?php foreach ($bebidas as $b): ?>
      <div class="product" data-categoria="Bebida">
        <img src="img/<?php echo htmlspecialchars(bebida_img($b)); ?>"
             alt="<?php echo htmlspecialchars($b['nombre']); ?>">
        <div class="product-txt">
          <h3><?php echo htmlspecialchars($b['nombre']); ?></h3>
          <p><?php echo htmlspecialchars($b['descripcion'] ?? ''); ?></p>
          <p class="precio"><?php echo number_format((float)$b['precio'], 0, ',', '.'); ?> COP</p>
          <a href="#" class="agregar-carrito btn-2" data-id="<?php echo (int)$b['id']; ?>">
            agregar al carrito
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<!-- Modal datos del cliente (mismo que index.php) -->
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

<script src="js/enviar.js"></script>
</body>
</html>
