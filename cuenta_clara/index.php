<?php
require 'config.php';
if (isset($_SESSION['modo'])) { header('Location: cuenta.php'); exit; }

$error  = $_SESSION['error'] ?? '';
$ultimo = $_SESSION['ultimo_usuario'] ?? '';
unset($_SESSION['error'], $_SESSION['ultimo_usuario']);

$titulo = 'Ingresar · Cuenta';
include 'cabecera.php';
?>
<main>
  <section class="login">
    <h1>Ingresa a tu cuenta</h1>
    <p class="sub">Consulta tu saldo y el historial de tus abonos.</p>
    <form action="login.php" method="post">
      <label for="usuario">Nombre de usuario</label>
      <input id="usuario" name="usuario" type="text" autocomplete="username" value="<?= e($ultimo) ?>" required>
      <label for="password">Contraseña</label>
      <div class="field">
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button class="toggle" id="ver" type="button" aria-label="Mostrar contraseña">Mostrar</button>
      </div>
      <p class="error" role="alert"><?= e($error) ?></p>
      <button class="primary" type="submit">Ingresar</button>
    </form>
    <p class="cambio">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>

    <div class="demo">
      <p><b>Cuenta de prueba</b></p>
      <p>Usuario: <code><?= e(DEMO_USUARIO) ?></code> · Contraseña: <code><?= e(DEMO_PASSWORD) ?></code></p>
      <button class="ghost" id="usarDemo" type="button" data-u="<?= e(DEMO_USUARIO) ?>" data-p="<?= e(DEMO_PASSWORD) ?>">Usar esta cuenta</button>
    </div>
  </section>
<?php include 'pie.php'; ?>
