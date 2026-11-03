<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gustavo Rivas Mestra · Proyectos</title>
<link rel="icon" href="cuenta_clara/assets/logo.png" type="image/png">
<style>
@font-face{font-family:'Archivo';src:url(cuenta_clara/assets/fonts/Archivo.woff2) format('woff2');font-weight:100 900;font-stretch:62% 125%;font-display:swap}
:root{--tinta:#1E1E23;--crema:#ECE6D7;--papel:#F6F2E8;--sombra:#C0B9AE;--muted:#5A564F}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;font:400 17px/1.5 'Archivo',system-ui,sans-serif;background:var(--crema);color:var(--tinta)}
main{max-width:760px;margin:0 auto;padding:56px 24px 48px}
h1{margin:0 0 6px;font-stretch:125%;font-weight:800;letter-spacing:-.02em;font-size:clamp(28px,7vw,44px);line-height:1.1}
.sub{margin:0 0 36px;color:var(--muted)}
.proyectos{display:grid;gap:20px}
.proyecto{display:block;padding:22px 24px;background:var(--papel);border:2px solid var(--tinta);color:inherit;text-decoration:none;box-shadow:6px 6px 0 var(--sombra);transition:transform .15s,box-shadow .15s}
.proyecto:hover,.proyecto:focus-visible{transform:translate(-2px,-2px);box-shadow:8px 8px 0 var(--sombra)}
.proyecto:focus-visible{outline:3px solid var(--tinta);outline-offset:3px}
.proyecto h2{margin:0 0 4px;font-stretch:125%;font-weight:800;letter-spacing:-.02em;font-size:22px}
.proyecto p{margin:0 0 14px;color:var(--muted)}
.ir{display:inline-block;min-height:44px;padding:9px 18px;background:var(--tinta);color:var(--crema);font-weight:600}
@media (min-width:700px){.proyectos{grid-template-columns:repeat(3,1fr)}.proyecto{display:flex;flex-direction:column}.proyecto p{flex:1}.ir{align-self:flex-start}}
</style>
</head>
<body>
<main>
  <h1>Gustavo Rivas Mestra</h1>
  <p class="sub">Elige el proyecto que quieres abrir.</p>
  <div class="proyectos">
    <a class="proyecto" href="cuenta_clara/index.php">
      <h2>App de banco</h2>
      <p>Consulta tu saldo, agrega dinero y revisa tus abonos.</p>
      <span class="ir">Abrir app</span>
    </a>
    <a class="proyecto" href="facturacion.php">
      <h2>Facturaci&oacute;n</h2>
      <p>Registra y consulta clientes y facturas.</p>
      <span class="ir">Abrir sistema</span>
    </a>
    <a class="proyecto" href="index.html">
      <h2>P&aacute;gina web</h2>
      <p>El Sistema Solar: un recorrido por nuestro vecindario en el espacio.</p>
      <span class="ir">Abrir p&aacute;gina</span>
    </a>
  </div>
</main>
</body>
</html>
