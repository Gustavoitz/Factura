<?php
require 'config.php';
if (!isset($_SESSION['modo'])) { header('Location: index.php'); exit; }

$demo = $_SESSION['modo'] === 'demo';
$abonos = []; $fallo = false;

// Mensaje de "abono registrado" (viene del redirect que sigue a un abono exitoso)
$ok = $_SESSION['ok'] ?? '';
unset($_SESSION['ok']);

// ---- Agregar dinero ----
$error = '';
$v = ['monto' => '', 'metodo' => 'Transferencia'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['monto']  = trim($_POST['monto'] ?? '');
    $v['metodo'] = $_POST['metodo'] ?? '';
    $monto = leer_monto($v['monto']);

    if (!csrf_valido($_POST['csrf'] ?? null)) {
        $error = 'El formulario venció. Inténtalo de nuevo.';
    } elseif ($monto === null || $monto <= 0) {
        $error = 'Escribe un monto válido: solo números y sin decimales.';
    } elseif ($monto > ABONO_MAXIMO) {
        $error = 'El máximo por abono es ' . pesos(ABONO_MAXIMO) . '.';
    } elseif (!in_array($v['metodo'], metodos_abono(), true)) {
        $error = 'Elige cómo vas a abonar.';
    } else {
        $fecha = date('Y-m-d');
        if ($demo) {
            // La cuenta de prueba no tiene base de datos: el abono vive solo en esta sesión
            $_SESSION['demo_abonos'][(int)$_SESSION['cliente_id']][] =
                ['fecha' => $fecha, 'metodo' => $v['metodo'], 'monto' => $monto];
        } else {
            try {
                $db = conectar();
                if (!$db) throw new RuntimeException('sin conexión');
                llamar_procedimiento($db, 'CALL sp_cuenta_agregar_abono(?,?,?,?)',
                    [(int)$_SESSION['cliente_id'], $fecha, $v['metodo'], $monto]);
            } catch (Throwable $ex) {
                error_log('cuenta_clara abono: ' . $ex->getMessage());
                $error = 'No pudimos registrar el abono. Inténtalo de nuevo en un momento.';
            }
        }
        if ($error === '') {
            $_SESSION['ok'] = 'Abono de ' . pesos($monto) . ' registrado.';
            header('Location: cuenta.php');   // así, al recargar la página, el abono no se repite
            exit;
        }
    }
}

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
  <a class="secundario" href="ahorros.php">Ir a Ahorros</a>
  <?php if ($ok): ?>
    <p class="exito" role="status"><?= e($ok) ?></p>
  <?php endif; ?>

  <?php if ($fallo): ?>
    <p class="error">No pudimos cargar tus datos. Recarga la página en un momento.</p>
  <?php else: ?>
  <?php if ($demo): ?>
    <p class="aviso">Estás viendo la cuenta de prueba. Sus datos son de ejemplo y los abonos que hagas aquí no se guardan: se borran al salir.</p>
  <?php endif; ?>

  <div class="cuerpo">
    <div class="izq">
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

    <section class="abonar">
      <h2>Agregar dinero</h2>
      <form action="cuenta.php" method="post" id="formAbono" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label for="monto">Monto a abonar</label>
        <input id="monto" name="monto" type="text" inputmode="numeric" autocomplete="off" placeholder="Ej. 150.000" value="<?= e($v['monto']) ?>" required>
        <div class="rapidos" aria-label="Montos rápidos">
          <?php foreach ([50000, 100000, 200000] as $r): ?>
          <button class="rapido" type="button" data-m="<?= $r ?>">+ <?= e(pesos($r)) ?></button>
          <?php endforeach; ?>
        </div>
        <fieldset class="metodos">
          <legend>¿Cómo abonas?</legend>
          <?php foreach (metodos_abono() as $i => $m): ?>
          <input id="metodo<?= $i ?>" name="metodo" type="radio" value="<?= e($m) ?>"<?= $v['metodo'] === $m ? ' checked' : '' ?>>
          <label for="metodo<?= $i ?>"><?= e($m) ?></label>
          <?php endforeach; ?>
        </fieldset>
        <p class="error" role="alert"><?= e($error) ?></p>
        <button class="primary" type="submit">Abonar</button>
      </form>
    </section>
    </div>

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
