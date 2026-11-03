<?php
require 'config.php';
if (!isset($_SESSION['modo'])) { header('Location: index.php'); exit; }

function leer_id(string $t): ?int {
    return ($t !== '' && ctype_digit($t) && strlen($t) <= 9 && (int)$t > 0) ? (int)$t : null;
}
// Los mensajes de los procedimientos (SIGNAL 45000, error 1644) se muestran tal cual; el resto, genérico.
function mensaje_bd(Throwable $ex): string {
    if ($ex->getCode() === 1644) return $ex->getMessage();
    error_log('cuenta_clara ahorros: ' . $ex->getMessage());
    return 'No pudimos completar la operación. Inténtalo de nuevo en un momento.';
}

// Mensaje de "listo" que viene del redirect posterior a un ahorro exitoso
$ok = $_SESSION['ok_ahorro'] ?? ''; $verId = $_SESSION['ver_id'] ?? null;
unset($_SESSION['ok_ahorro'], $_SESSION['ver_id']);

$error = ''; $cliente = null; $clientes = []; $fallo = false; $id = null;
$v = ['id' => $verId ? (string)$verId : '', 'valor' => ''];
$db = conectar_ahorros();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['id'] = trim($_POST['id'] ?? ''); $v['valor'] = trim($_POST['valor'] ?? '');
    $accion = ($_POST['accion'] ?? '') === 'consultar' ? 'consultar' : 'ahorrar';
    $id = leer_id($v['id']); $valor = leer_monto($v['valor']);

    if (!csrf_valido($_POST['csrf'] ?? null)) { $error = 'El formulario venció. Inténtalo de nuevo.'; $id = null; }
    elseif ($id === null) $error = 'Escribe el id del cliente (un número).';
    elseif (!$db) $error = 'No hay conexión con la base de datos. Inténtalo en un momento.';
    elseif ($accion === 'ahorrar' && ($valor === null || $valor <= 0)) $error = 'Escribe un valor válido: solo números y sin decimales.';
    elseif ($accion === 'ahorrar' && $valor > ABONO_MAXIMO) $error = 'El máximo por operación es ' . pesos(ABONO_MAXIMO) . '.';
    elseif ($accion === 'ahorrar') {
        try {
            llamar_procedimiento($db, 'CALL sp_ahorrar(?,?)', [$id, $valor]);
            $_SESSION['ok_ahorro'] = pesos($valor) . ' pasaron del saldo al ahorro del cliente ' . $id . '.';
            $_SESSION['ver_id'] = $id;
            header('Location: ahorros.php');   // así, al recargar la página, el dinero no se mueve otra vez
            exit;
        } catch (mysqli_sql_exception $ex) { $error = mensaje_bd($ex); }
    }
} elseif ($verId) { $id = (int)$verId; }

if ($db && $id !== null) {
    try {
        $r = llamar_procedimiento($db, 'CALL sp_ahorros_consultar(?)', [$id]);
        $cliente = $r->result ? $r->result->fetch_assoc() : null;
    } catch (mysqli_sql_exception $ex) { if ($error === '') $error = mensaje_bd($ex); }
}

if ($db) {
    try {
        $r = llamar_procedimiento($db, 'CALL sp_ahorros_listar()');
        $clientes = $r->result ? $r->result->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $ex) { error_log('cuenta_clara ahorros listar: ' . $ex->getMessage()); $fallo = true; }
} else { $fallo = true; }

$titulo = 'Ahorros · Gustavo Rivas';
include 'cabecera.php';
?>
<main>
  <h1>Ahorros</h1>
  <p class="sub">Escribe el id del cliente y el valor que pasa de su saldo a su ahorro.</p>
  <a class="secundario" href="cuenta.php">← Volver a mi cuenta</a>
  <?php if ($ok): ?>
    <p class="exito" role="status"><?= e($ok) ?></p>
  <?php endif; ?>

  <?php if ($fallo): ?>
    <p class="error">No pudimos cargar los datos de ahorros. Recarga la página en un momento.</p>
  <?php else: ?>
  <div class="cuerpo">
    <div class="izq">
    <section class="saldo">
      <?php if ($cliente): ?>
        <p class="rotulo">Saldo del cliente <?= (int)$cliente['id'] ?></p>
        <span class="total" id="total" data-total="<?= (int)$cliente['saldo'] ?>"><?= pesos($cliente['saldo']) ?></span>
        <p class="detalle">Ahorro: <?= pesos($cliente['ahorro']) ?></p>
        <?php if ((int)$cliente['saldo'] === 0): ?>
          <p class="sin-saldo" role="alert">Este cliente no tiene saldo disponible.</p>
        <?php endif; ?>
      <?php else: ?>
        <p class="rotulo">Cliente</p>
        <p class="detalle">Escribe un id y pulsa «Consultar cliente» o «Ahorrar».</p>
      <?php endif; ?>
    </section>

    <section class="abonar">
      <h2>Pasar dinero al ahorro</h2>
      <form action="ahorros.php" method="post" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label for="cid">Id del cliente</label>
        <input id="cid" name="id" type="text" inputmode="numeric" autocomplete="off" placeholder="Ej. 1" value="<?= e($v['id']) ?>" required>
        <label for="monto">Valor a ahorrar</label>
        <input id="monto" name="valor" type="text" inputmode="numeric" autocomplete="off" placeholder="Ej. 50.000" value="<?= e($v['valor']) ?>">
        <div class="rapidos" aria-label="Valores rápidos">
          <?php foreach ([50000, 100000, 200000] as $r): ?>
          <button class="rapido" type="button" data-m="<?= $r ?>"><?= e(pesos($r)) ?></button>
          <?php endforeach; ?>
        </div>
        <p class="error" role="alert"><?= e($error) ?></p>
        <button class="primary" type="submit" name="accion" value="ahorrar">Ahorrar</button>
        <button class="secundario" type="submit" name="accion" value="consultar">Consultar cliente</button>
      </form>
    </section>
    </div>

    <section class="historial">
      <h2>Clientes</h2>
      <p class="vacio">Toca un cliente para elegir su id.</p>
      <ul class="lista">
        <?php foreach ($clientes as $c): ?>
        <li data-id="<?= (int)$c['id'] ?>" tabindex="0">
          <span class="f">Cliente <?= (int)$c['id'] ?></span>
          <span class="m">Saldo <?= pesos($c['saldo']) ?></span>
          <span class="v">Ahorro <?= pesos($c['ahorro']) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </section>
  </div>
  <script>
  document.querySelectorAll('.lista li[data-id]').forEach(function (li) {
    function elegir() { document.getElementById('cid').value = li.dataset.id; document.getElementById('monto').focus(); }
    li.addEventListener('click', elegir);
    li.addEventListener('keydown', function (e) { if (e.key === 'Enter') elegir(); });
  });
  </script>
  <?php endif; ?>
<?php include 'pie.php'; ?>
