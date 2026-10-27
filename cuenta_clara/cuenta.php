<?php
require 'config.php';
if (!isset($_SESSION['modo'])) { header('Location: index.php'); exit; }

$demo = $_SESSION['modo'] === 'demo';
$abonos = []; $fallo = false;

if ($demo) {
    $abonos = demo_abonos((int)$_SESSION['cliente_id']);
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

// Agrupa el historial por mes (ya viene del más reciente al más antiguo)
$meses = [];
foreach ($abonos as $a) {
    $k = substr($a['fecha'], 0, 7);
    $meses[$k]['titulo'] = $meses[$k]['titulo'] ?? mes_anio($a['fecha']);
    $meses[$k]['suma']   = ($meses[$k]['suma'] ?? 0) + (int)$a['monto'];
    $meses[$k]['filas'][] = $a;
}

$titulo = 'Mi cuenta · Gustavo Rivas';
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

  <div class="cuerpo">
    <section class="saldo">
      <p class="rotulo">Total en tu cuenta</p>
      <span class="total" id="total" data-total="<?= $total ?>"><?= pesos($total) ?></span>
      <?php if ($n): ?>
        <p class="detalle"><?= $n . ($n === 1 ? ' abono' : ' abonos') ?></p>
        <p class="detalle">Último abono: <?= e(fecha_corta($abonos[0]['fecha'])) ?></p>
      <?php else: ?>
        <p class="detalle">Todavía no tienes abonos registrados.</p>
      <?php endif; ?>
    </section>

    <section class="historial">
      <h2>Historial de abonos</h2>
      <?php if (!$n): ?>
        <p class="vacio">Cuando hagas tu primer abono, aparecerá aquí.</p>
      <?php endif; ?>
      <?php foreach ($meses as $mes): ?>
      <div class="mes">
        <div class="mes-cab"><h3><?= e($mes['titulo']) ?></h3><span><?= pesos($mes['suma']) ?></span></div>
        <ul class="lista">
          <?php foreach ($mes['filas'] as $a): ?>
          <li>
            <span class="f"><?= e(fecha_dia($a['fecha'])) ?></span>
            <span class="m"><?= e($a['metodo']) ?></span>
            <span class="v">+<?= pesos($a['monto']) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </section>
  </div>
  <?php endif; ?>
<?php include 'pie.php'; ?>
