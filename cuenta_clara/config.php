<?php
session_start();

/* La conexión a la base de datos viene de ../conexion.php (la carpeta de atrás).
   Si ese archivo no existe, falta DB_PASSWORD o no se puede conectar, la página funciona
   igual en modo de prueba: solo entran las cuentas demo (ver demo_cuentas() más abajo). */

// Devuelve la conexión de ../conexion.php o null si no hay base de datos (nunca lanza error).
function conectar(): ?mysqli {
    static $db = false;                 // se conecta una sola vez por visita
    if ($db !== false) return $db;
    $db = null;

    $archivo = __DIR__ . '/../conexion.php';
    $clave = getenv('DB_PASSWORD');
    if (!is_file($archivo) || $clave === false || $clave === '') return $db;   // conexion.php haría die()

    try {
        ini_set('mysqli.connect_timeout', '3');
        if (!defined('CONEXION_SILENCIOSA')) define('CONEXION_SILENCIOSA', true);
        $conexion = null;
        $nivel = error_reporting(0);
        include $archivo;               // define $conexion
        error_reporting($nivel);
        if ($conexion instanceof mysqli) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);   // conexion.php lo apaga; aquí usamos excepciones
            $db = $conexion;
        }
    } catch (Throwable $ex) {
        $db = null;
    }
    return $db;
}

// Cuentas de prueba (no necesitan base de datos). Los ids son negativos para no chocar con los de la BD.
function demo_cuentas(): array {
    return [
        ['id' => -1, 'usuario' => 'demo',  'password' => '1234', 'nombres' => 'Camila',    'apellidos' => 'Torres',
         'abonos' => [
            ['fecha' => '2026-10-20', 'metodo' => 'Transferencia', 'monto' => 150000],
            ['fecha' => '2026-10-05', 'metodo' => 'Efectivo',      'monto' => 80000],
            ['fecha' => '2026-09-18', 'metodo' => 'Nequi',         'monto' => 120000],
            ['fecha' => '2026-09-02', 'metodo' => 'Efectivo',      'monto' => 60000],
            ['fecha' => '2026-08-15', 'metodo' => 'Transferencia', 'monto' => 200000],
            ['fecha' => '2026-08-01', 'metodo' => 'Nequi',         'monto' => 90000],
         ]],
        ['id' => -2, 'usuario' => 'demo2', 'password' => '1234', 'nombres' => 'Mateo',     'apellidos' => 'Gómez',
         'abonos' => [
            ['fecha' => '2026-10-24', 'metodo' => 'Nequi',         'monto' => 45000],
            ['fecha' => '2026-10-10', 'metodo' => 'Efectivo',      'monto' => 70000],
            ['fecha' => '2026-09-26', 'metodo' => 'Transferencia', 'monto' => 110000],
            ['fecha' => '2026-09-12', 'metodo' => 'Efectivo',      'monto' => 55000],
         ]],
        ['id' => -3, 'usuario' => 'demo3', 'password' => '1234', 'nombres' => 'Valentina', 'apellidos' => 'Restrepo',
         'abonos' => [
            ['fecha' => '2026-10-26', 'metodo' => 'Transferencia', 'monto' => 300000],
            ['fecha' => '2026-10-12', 'metodo' => 'Nequi',         'monto' => 85000],
            ['fecha' => '2026-10-03', 'metodo' => 'Efectivo',      'monto' => 120000],
            ['fecha' => '2026-09-21', 'metodo' => 'Transferencia', 'monto' => 250000],
            ['fecha' => '2026-09-07', 'metodo' => 'Nequi',         'monto' => 65000],
            ['fecha' => '2026-08-28', 'metodo' => 'Efectivo',      'monto' => 140000],
            ['fecha' => '2026-08-09', 'metodo' => 'Transferencia', 'monto' => 180000],
            ['fecha' => '2026-07-22', 'metodo' => 'Nequi',         'monto' => 95000],
            ['fecha' => '2026-07-04', 'metodo' => 'Efectivo',      'monto' => 210000],
         ]],
        ['id' => -4, 'usuario' => 'demo4', 'password' => '1234', 'nombres' => 'Santiago',  'apellidos' => 'Herrera',
         'abonos' => [
            ['fecha' => '2026-10-15', 'metodo' => 'Efectivo',      'monto' => 50000],
         ]],
    ];
}

// Devuelve la cuenta demo que coincide con usuario y contraseña, o null.
function demo_buscar(string $usuario, string $password): ?array {
    foreach (demo_cuentas() as $c) {
        if (hash_equals($c['usuario'], $usuario) && hash_equals($c['password'], $password)) return $c;
    }
    return null;
}

// ¿Este nombre de usuario está reservado por una cuenta demo?
function demo_reservado(string $usuario): bool {
    foreach (demo_cuentas() as $c) {
        if (strcasecmp($c['usuario'], $usuario) === 0) return true;
    }
    return false;
}

// Abonos de una cuenta demo por su id, del más reciente al más antiguo.
function demo_abonos(int $id): array {
    foreach (demo_cuentas() as $c) {
        if ($c['id'] === $id) {
            $a = $c['abonos'];
            usort($a, fn($x, $y) => strcmp($y['fecha'], $x['fecha']));
            return $a;
        }
    }
    return [];
}

function e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function pesos($n): string {
    return '$ ' . number_format((float)$n, 0, ',', '.');
}

function fecha_corta(string $f): string {
    static $m = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    $d = new DateTime($f);
    return $d->format('j') . ' ' . $m[(int)$d->format('n') - 1] . ' ' . $d->format('Y');
}

// "20 oct" (día y mes abreviado) para las filas del historial.
function fecha_dia(string $f): string {
    static $m = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    $d = new DateTime($f);
    return $d->format('j') . ' ' . $m[(int)$d->format('n') - 1];
}

// "octubre 2026" para el título de cada mes del historial.
function mes_anio(string $f): string {
    static $m = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $d = new DateTime($f);
    return $m[(int)$d->format('n') - 1] . ' ' . $d->format('Y');
}

// Inicia la sesión del cliente y va a su cuenta. $modo: 'db' o 'demo'.
function iniciar_sesion(string $modo, int $id, array $n): void {
    session_regenerate_id(true);
    unset($_SESSION['ultimo_usuario']);
    $_SESSION['modo']       = $modo;
    $_SESSION['cliente_id'] = $id;
    $_SESSION['primer']     = $n['nombres'];
    $_SESSION['nombre']     = trim($n['nombres'] . ' ' . $n['apellidos']);
    header('Location: cuenta.php');
    exit;
}
