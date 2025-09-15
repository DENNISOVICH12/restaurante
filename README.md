# Migración del proyecto "Restaurante" a Laravel + Docker

Este repositorio ahora incluye la configuración base para levantar el proyecto con **Laravel 10**, **PostgreSQL** y **Nginx** sobre Docker. También se agregan ejemplos de modelos, migraciones, controladores y vistas para que adaptes tu lógica actual desarrollada en PHP puro.

## 1. Arquitectura de contenedores

`docker-compose.yml` define tres servicios conectados por la red `appnet`:

| Servicio | Rol | Imagen / Build | Puertos expuestos |
|----------|-----|----------------|-------------------|
| `app`    | Ejecuta PHP-FPM y Composer para Laravel. Se construye con el `Dockerfile` que instala las extensiones de PostgreSQL. | `php:8.2-fpm` | — |
| `nginx`  | Servidor web que sirve el contenido de `public/` y reenvía PHP a `app:9000`. | `nginx:1.25-alpine` | `8080:80` |
| `db`     | Base de datos PostgreSQL con la base `restaurante1`. | `postgres:15` | `5432:5432` |

La carpeta del proyecto se monta en `/var/www/html` dentro de `app` y `nginx`, por lo que cualquier cambio local se refleja inmediatamente.

## 2. Puesta en marcha

1. Crea el archivo `.env` de Laravel (lo harás dentro del contenedor más adelante). Si ya existe una aplicación Laravel solo asegúrate de que esté en la raíz del proyecto.
2. Construye las imágenes y levanta los contenedores:

   ```bash
   docker compose build
   docker compose up -d
   ```

3. Entra al contenedor de la aplicación para ejecutar Composer/Artisan:

   ```bash
   docker compose exec app bash
   ```

   Verás un prompt similar a `www-data@<id>:/var/www/html$`.

## 3. Instalar Laravel dentro del contenedor

Si todavía no tienes Laravel creado en esta carpeta:

```bash
composer create-project laravel/laravel .
php artisan key:generate
```

Después copia `.env.example` a `.env` y edita los valores de base de datos (ver sección siguiente). Finalmente instala dependencias front-end (si usas Breeze o Vite):

```bash
npm install
npm run build   # o npm run dev para modo desarrollo
```

> **Tip:** Si no tienes Node instalado en tu host puedes ejecutar los comandos anteriores con una imagen oficial: `docker run --rm -it -v $(pwd):/app -w /app node:20 npm install`.

## 4. Conectar Laravel a PostgreSQL (`restaurante1`)

En el archivo `.env` configura:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=restaurante1
DB_USERNAME=postgres
DB_PASSWORD=123456
```

Al estar todos los servicios en la misma red de Docker, el host de la base es `db` (nombre del servicio en `docker-compose`).

## 5. Migraciones y modelos para tus tablas existentes

En `laravel/database/migrations/` tienes plantillas de migraciones para las tablas `menu_items`, `clientes`, `pedidos`, `detalle_pedido` y `usuarios`. Cada migración revisa si la tabla ya existe para evitar sobreescribir datos en una base existente. Copia esos archivos dentro de `database/migrations/` de tu proyecto Laravel y ejecútalos con:

```bash
docker compose exec app php artisan migrate
```

Si ya tienes datos cargados, las migraciones simplemente no crearán las tablas. El archivo `2024_01_01_000050_update_usuarios_table_for_breeze.php` añade las columnas `email_verified_at` y `remember_token` solo si no existen, necesarias para Breeze.

Los modelos Eloquent listos para usar están en `laravel/app/Models/`:

- `MenuItem` gestiona `menu_items` y expone el scope `disponibles()`.
- `Pedido`, `DetallePedido` y `Cliente` definen las relaciones entre pedidos y clientes.
- `User` está adaptado para leer/escribir en la tabla `usuarios`, mapeando `nombre`⇔`name` y `correo`⇔`email`.

Copia cada archivo a `app/Models/` en tu aplicación. No olvides ejecutar `composer dump-autoload` si agregas nuevos namespaces.

## 6. Controladores y rutas de ejemplo

Los controladores de muestra están en `laravel/app/Http/Controllers/`:

- `MenuItemController@index` lista platos y bebidas, permite filtrar por categoría y responde HTML o JSON.
- `OrderController@store` recibe el JSON enviado por tu `enviar.js`, crea/actualiza el cliente, guarda el pedido y sus detalles en una transacción y devuelve un JSON de confirmación.

Añade las rutas correspondientes copiando `laravel/routes/web.php` a `routes/web.php` en tu proyecto:

```php
Route::get('/', [MenuItemController::class, 'index'])->name('menu.index');
Route::get('/menu', [MenuItemController::class, 'index'])->name('menu.list');
Route::post('/pedidos', [OrderController::class, 'store'])->name('pedidos.store');
```

Para exponer un endpoint de solo API puedes mover la ruta POST a `routes/api.php` y consumirla desde JavaScript mediante `/api/pedidos`.

## 7. Ubicación de vistas y assets

Laravel espera las vistas en `resources/views`. El archivo `laravel/resources/views/menu/index.blade.php` muestra cómo listar productos y cómo referenciar assets estáticos usando `asset()`:

```html
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<script src="{{ asset('js/enviar.js') }}" defer></script>
```

Coloca tus archivos JS/CSS sin procesar en `public/js` y `public/css` (por ejemplo, mueve `js/enviar.js` → `public/js/enviar.js`). Si usas Vite puedes importar esos assets desde `resources/js` y `resources/css`, pero la referencia con `asset()` funciona perfecto si no necesitas compilación.

Para imágenes compartidas (como tu carpeta `img/`) puedes moverlas a `public/img` o crear un enlace simbólico con `php artisan storage:link` si prefieres almacenarlas en `storage/app/public`.

## 8. Autenticación con Laravel Breeze usando `usuarios`

1. Instala Breeze dentro del contenedor `app`:

   ```bash
   composer require laravel/breeze --dev
   php artisan breeze:install
   npm install
   npm run build
   ```

2. Copia el modelo `laravel/app/Models/User.php` sobre `app/Models/User.php` de tu proyecto para que Breeze utilice la tabla `usuarios`.
3. Ajusta `config/auth.php` para que el provider `users` apunte a ese modelo:

   ```php
   'providers' => [
       'users' => [
           'driver' => 'eloquent',
           'model' => App\Models\User::class,
       ],
   ],
   ```

4. Actualiza las requests generadas por Breeze para usar `correo` en lugar de `email`. En `app/Http/Requests/Auth/LoginRequest.php` cambia:

   ```php
   public function credentials()
   {
       return [
           'correo' => $this->get('correo'),
           'password' => $this->get('password'),
       ];
   }
   ```

   y adapta las vistas de registro/login (por ejemplo `resources/views/auth/register.blade.php`) para mostrar los campos `nombre` y `correo`. El modelo ya convierte automáticamente `correo`⇔`email`, por lo que Fortify seguirá funcionando.

5. Ejecuta `php artisan migrate` para asegurarte de que las columnas requeridas por Breeze (`remember_token`, `email_verified_at`) existan en `usuarios`.

## 9. Ejecutar comandos Artisan y Composer dentro de Docker

- **Instalar dependencias**: `docker compose exec app composer install`
- **Crear modelos/controladores**: `docker compose exec app php artisan make:model Pedido -m`
- **Ejecutar migraciones**: `docker compose exec app php artisan migrate`
- **Lanzar seeders**: `docker compose exec app php artisan db:seed`
- **Limpiar cachés**: `docker compose exec app php artisan optimize:clear`

Cuando necesites detener todo: `docker compose down` (añade `-v` para eliminar volúmenes si quieres reiniciar la base).

## 10. Flujo sugerido de migración

1. Levanta los contenedores.
2. Instala Laravel dentro del contenedor `app` y configura `.env`.
3. Copia los modelos, controladores, rutas y vistas de la carpeta `laravel/` de este repositorio a tu nueva aplicación.
4. Ajusta las vistas Blade para que repliquen tu HTML actual y mueve los assets a `public/` o `resources/` según prefieras.
5. Ejecuta `php artisan migrate` para crear/actualizar las tablas necesarias.
6. Instala Laravel Breeze y reemplaza tu flujo de login/registro con el sistema generado.
7. Prueba las rutas desde el navegador en `http://localhost:8080`.

Con esta estructura tendrás tu proyecto corriendo en Laravel sobre Docker, manteniendo PostgreSQL como base de datos y preparando el camino para extender funcionalidades con el ecosistema del framework.
