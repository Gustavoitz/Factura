<?php
require 'config.php';
if (!isset($_SESSION['modo'])) { header('Location: index.php'); exit; }

$demo = $_SESSION['modo'] === 'demo';
$abonos = []; $fallo = false;

if ($demo) {
    $abonos = demo_abonos();
} else {
    try {
        $db = conectar();
        if (!$db) throw new RuntimeException('sin conexión');
        $r = llamar_procedimiento($db, 'CALL sp_cuenta_listar_abonos(?)', [$_SESSION['cliente_id']]);
        $abonos = $r->result ? $r->result->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $ex) {
        $fallo = true;
    }
}
$total = (int)array_sum(array_column($abonos, 'monto'));
$n = count($abonos);

$titulo = 'Mi cuenta · Cuenta';
include 'cabecera.php';
?>
<main>
  <h1>Hola, <?= e($_SESSION['primer']) ?></h1>
  <p class="sub">Así va tu cuenta hasta hoy.</p>

  <?php if ($fallo): ?>
    <p class="error">No pudimos cargar tus datos. Recarga la página en un momento.</p>
  <?php else: ?>
  <?php if ($demo): ?>
    <p class="aviso">Estás viendo la cuenta de prueba. Sus datos son de ejemplo.</p>
  <?php endif; ?>
  <div class="balance">
    <p>Total en tu cuenta</p>
    <span class="total" id="total" data-total="<?= $total ?>"><?= pesos($total) ?></span>
    <p><?= $n
        ? $n . ($n === 1 ? ' abono' : ' abonos') . ' · último el ' . e(fecha_corta($abonos[0]['fecha']))
        : 'Todavía no tienes abonos registrados.' ?></p>
  </div>

  <h2>Historial de abonos</h2>
  <ul class="list">
    <?php if (!$n): ?>
      <li class="empty">Cuando hagas tu primer abono, aparecerá aquí.</li>
    <?php endif; ?>
    <?php foreach ($abonos as $a): ?>
      <li>
        <span><?= e(fecha_corta($a['fecha'])) ?></span>
        <span class="m"><?= e($a['metodo']) ?></span>
        <span class="v">+<?= pesos($a['monto']) ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
<?php include 'pie.php'; ?>
