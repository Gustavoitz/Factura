<?php
session_start();

/* La conexión a la base de datos viene de ../conexion.php (la carpeta de atrás).
   Si ese archivo no existe, falta DB_PASSWORD o no se puede conectar, la página funciona
   igual en modo de prueba: solo entra la cuenta demo (usuario "demo", contraseña "1234"). */
const DEMO_USUARIO  = 'demo';
const DEMO_PASSWORD = '1234';

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

// Datos de la cuenta de prueba (no necesitan base de datos).
function demo_nombre(): array {
    return ['nombres' => 'Camila', 'apellidos' => 'Torres'];
}

function demo_abonos(): array {
    return [
        ['fecha' => '2026-10-20', 'metodo' => 'Transferencia', 'monto' => 150000],
        ['fecha' => '2026-10-05', 'metodo' => 'Efectivo',      'monto' => 80000],
        ['fecha' => '2026-09-18', 'metodo' => 'Nequi',         'monto' => 120000],
        ['fecha' => '2026-09-02', 'metodo' => 'Efectivo',      'monto' => 60000],
        ['fecha' => '2026-08-15', 'metodo' => 'Transferencia', 'monto' => 200000],
        ['fecha' => '2026-08-01', 'metodo' => 'Nequi',         'monto' => 90000],
    ];
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
