<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

$usuario  = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';
$_SESSION['ultimo_usuario'] = $usuario;

function volver(string $msg): void { $_SESSION['error'] = $msg; header('Location: index.php'); exit; }

if ($usuario === '' || $password === '') volver('Escribe tu usuario y tu contraseña.');

// Las cuentas de prueba siempre están disponibles, con o sin base de datos
if ($c = demo_buscar($usuario, $password)) {
    iniciar_sesion('demo', $c['id'], $c);
}

// Base de datos (todo por procedimientos almacenados)
$hayBD = false; $fila = null;
if ($db = conectar()) {
    try {
        $r = llamar_procedimiento($db, 'CALL sp_cuenta_buscar_cliente(?)', [$usuario]);
        $fila  = $r->result ? $r->result->fetch_assoc() : null;
        $hayBD = $r->ok;
    } catch (Throwable $ex) {
        error_log('cuenta_clara login: ' . $ex->getMessage());
        $hayBD = false;   // por ejemplo, los procedimientos todavía no existen
    }
}

if (!$hayBD) volver('No hay conexión con la base de datos. Por ahora solo puedes entrar con la cuenta de prueba.');

if ($fila && password_verify($password, $fila['pwd_hash'])) {
    iniciar_sesion('db', (int)$fila['id'], $fila);
}
volver('Usuario o contraseña incorrectos.');
