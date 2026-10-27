<?php
require 'config.php';
if (isset($_SESSION['modo'])) { header('Location: cuenta.php'); exit; }

$error  = $_SESSION['error'] ?? '';
$ultimo = $_SESSION['ultimo_usuario'] ?? '';
unset($_SESSION['error'], $_SESSION['ultimo_usuario']);

$titulo = 'Ingresar · Gustavo Rivas';
include 'cabecera.php';
?>
<main>
  <section class="acceso">
    <div class="intro">
      <h1>Ingresa a tu cuenta</h1>
      <p class="sub">Consulta tu saldo y el historial de tus abonos.</p>
    </div>
    <div class="hoja">
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
        <p><b>Cuentas de prueba</b></p>
        <ul class="demo-lista">
          <?php foreach (demo_cuentas() as $c): ?>
          <li>
            <div>
              <p><?= e($c['nombres'] . ' ' . $c['apellidos']) ?></p>
              <p>Usuario: <code><?= e($c['usuario']) ?></code> · Contraseña: <code><?= e($c['password']) ?></code></p>
            </div>
            <button class="ghost usarDemo" type="button" data-u="<?= e($c['usuario']) ?>" data-p="<?= e($c['password']) ?>">Usar</button>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </section>
<?php include 'pie.php'; ?>
