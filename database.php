<?php
/**
 * database.php — Conexión centralizada a PostgreSQL (Docker)
 * - Usa variables de entorno si existen; si no, valores por defecto.
 * - Reintentos con backoff exponencial.
 * - Helpers con consultas parametrizadas y manejo de errores.
 */

// ===== Config =====
$host     = getenv('PGHOST')     ?: 'db';          // nombre del servicio en docker-compose
$port     = getenv('PGPORT')     ?: '5432';
$dbname   = getenv('PGDATABASE') ?: 'restaurante1';
$user     = getenv('PGUSER')     ?: 'postgres';
$password = getenv('PGPASSWORD') ?: '123456';

// Añadimos connect_timeout y deshabilitamos SSL en local (ajústalo si usas SSL)
$conn_string = sprintf(
    "host=%s port=%s dbname=%s user=%s password=%s connect_timeout=5 sslmode=disable",
    $host, $port, $dbname, $user, $password
);

// ===== Conexión con reintentos =====
$conexion = @pg_connect($conn_string);
$tries = 0;
$maxTries = 12; // ~ (5-40s total con backoff)

while (!$conexion && $tries < $maxTries) {
    // Backoff exponencial: 1, 2, 3, 4... segundos
    $wait = min(1 + $tries, 4); // tope 4s entre intentos
    sleep($wait);
    $conexion = @pg_connect($conn_string);
    $tries++;
}

if (!$conexion) {
    http_response_code(500);
    // No llamar pg_last_error() sin conexión para evitar warnings
    die("❌ No fue posible conectar a PostgreSQL en {$host}:{$port} tras {$tries} intentos.");
}

// Codificación UTF-8
pg_set_client_encoding($conexion, "UTF8");

// ===== Helpers seguros =====

/**
 * Ejecuta una consulta segura.
 * - Si $params está vacío -> pg_query
 * - Si $params tiene valores -> pg_query_params (evita inyección)
 * Retorna resource de resultado o detiene con error 500.
 */
function db_query(string $sql, array $params = [])
{
    global $conexion;

    $res = empty($params)
        ? @pg_query($conexion, $sql)
        : @pg_query_params($conexion, $sql, $params);

    if ($res === false) {
        $err = pg_last_error($conexion);
        http_response_code(500);
        die("❌ Error en la consulta:\nSQL: {$sql}\nDetalle: {$err}");
    }
    return $res;
}

/** Retorna todas las filas (assoc) de una consulta. */
function db_fetch_all(string $sql, array $params = []): array
{
    $res = db_query($sql, $params);
    $rows = [];
    while ($row = pg_fetch_assoc($res)) {
        $rows[] = $row;
    }
    return $rows;
}

/** Retorna una sola fila (assoc) o null. */
function db_fetch_one(string $sql, array $params = []): ?array
{
    $res = db_query($sql, $params);
    $row = pg_fetch_assoc($res);
    return $row === false ? null : $row;
}

/** Ejecuta y devuelve número de filas afectadas (para UPDATE/DELETE). */
function db_exec(string $sql, array $params = []): int
{
    $res = db_query($sql, $params);
    return pg_affected_rows($res);
}

/** Helpers de transacción. */
function db_begin(): void   { db_query("BEGIN"); }
function db_commit(): void  { db_query("COMMIT"); }
function db_rollback(): void{ db_query("ROLLBACK"); }

/**
 * Inserta con RETURNING y devuelve la fila devuelta (ideal para obtener el id).
 * Ejemplo:
 *   $row = db_insert_returning(
 *     "INSERT INTO pedidos(id_cliente, estado) VALUES($1, $2) RETURNING id",
 *     [$idCliente, 'pendiente']
 *   );
 *   $nuevoId = $row['id'];
 */
function db_insert_returning(string $sql, array $params = []): array
{
    $row = db_fetch_one($sql, $params);
    if ($row === null) {
        http_response_code(500);
        die("❌ INSERT RETURNING no devolvió fila.");
    }
    return $row;
}

// ===== Sesión =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
