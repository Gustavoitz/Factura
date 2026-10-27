<?php
require 'config.php';
if (isset($_SESSION['modo'])) { header('Location: cuenta.php'); exit; }

$sinBD = 'El registro necesita la base de datos y ahora no hay conexión. Mientras tanto puedes entrar con la cuenta de prueba.';
$error = '';
$v = ['nombres' => '', 'apellidos' => '', 'usuario' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['nombres']   = trim($_POST['nombres'] ?? '');
    $v['apellidos'] = trim($_POST['apellidos'] ?? '');
    $v['usuario']   = trim($_POST['usuario'] ?? '');
    $pass = $_POST['password'] ?? '';
    $conf = $_POST['confirmar'] ?? '';

    if ($v['nombres'] === '' || $v['apellidos'] === '' || $v['usuario'] === '' || $pass === '') {
        $error = 'Completa todos los campos.';
    } elseif (mb_strlen($v['nombres']) > 80 || mb_strlen($v['apellidos']) > 80) {
        $error = 'Los nombres y los apellidos pueden tener hasta 80 caracteres.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $v['usuario'])) {
        $error = 'El usuario debe tener de 3 a 40 caracteres: letras, números, punto, guion o guion bajo.';
    } elseif (demo_reservado($v['usuario'])) {
        $error = 'Ese nombre de usuario no está disponible.';
    } elseif (strlen($pass) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($pass !== $conf) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (!($db = conectar())) {
        $error = $sinBD;
    } else {
        try {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $r = llamar_procedimiento($db, 'CALL sp_cuenta_registrar_cliente(?,?,?,?)',
                [$v['usuario'], $hash, $v['nombres'], $v['apellidos']]);
            $id = $r->result ? (int)($r->result->fetch_assoc()['id'] ?? 0) : 0;

            if ($id === 0) {
                $error = 'Ese nombre de usuario ya está en uso.';
            } else {
                iniciar_sesion('db', $id, $v);
            }
        } catch (Throwable $ex) {
            $error = $sinBD;
        }
    }
}

$titulo = 'Crear cuenta · Gustavo Rivas';
include 'cabecera.php';
?>
<main>
  <section class="acceso">
    <div class="intro">
      <h1>Crea tu cuenta</h1>
      <p class="sub">Regístrate para consultar tu saldo y tus abonos.</p>
    </div>
    <div class="hoja">
      <form action="registro.php" method="post">
        <label for="nombres">Nombres</label>
        <input id="nombres" name="nombres" type="text" autocomplete="given-name" maxlength="80" value="<?= e($v['nombres']) ?>" required>
        <label for="apellidos">Apellidos</label>
        <input id="apellidos" name="apellidos" type="text" autocomplete="family-name" maxlength="80" value="<?= e($v['apellidos']) ?>" required>
        <label for="usuario">Nombre de usuario</label>
        <input id="usuario" name="usuario" type="text" autocomplete="username" maxlength="40" value="<?= e($v['usuario']) ?>" required>
        <label for="password">Contraseña</label>
        <div class="field">
          <input id="password" name="password" type="password" autocomplete="new-password" minlength="6" required>
          <button class="toggle" id="ver" type="button" aria-label="Mostrar contraseña">Mostrar</button>
        </div>
        <p class="ayuda">Mínimo 6 caracteres.</p>
        <label for="confirmar">Repite la contraseña</label>
        <input id="confirmar" name="confirmar" type="password" autocomplete="new-password" required>
        <p class="error" role="alert"><?= e($error) ?></p>
        <button class="primary" type="submit">Crear cuenta</button>
      </form>
      <p class="cambio">¿Ya tienes cuenta? <a href="index.php">Ingresa</a></p>
    </div>
  </section>
<?php include 'pie.php'; ?>
