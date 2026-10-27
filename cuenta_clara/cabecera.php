<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#C3B09B">
<title><?= e($titulo ?? 'Mi cuenta · Gustavo Rivas') ?></title>
<link rel="icon" href="assets/logo.png" type="image/png">
<link rel="preload" href="assets/fonts/Archivo.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="assets/estilos.css">
<script>try{if(sessionStorage.getItem('cc_loader'))document.documentElement.classList.add('sin-loader')}catch(e){}</script>
<noscript><style>header,main,.marca{opacity:1!important}#loader,#logo{display:none}#slot{background:url(assets/logo.png) center/contain no-repeat}</style></noscript>
</head>
<body>
<header>
  <a id="slot" href="index.php" aria-label="Inicio"></a>
  <div class="marca">
    <span class="brand">Gustavo Rivas</span>
    <span class="rol">Cuenta de cliente</span>
  </div>
  <?php if (isset($_SESSION['modo'])): ?>
  <div id="who"><span><?= e($_SESSION['nombre']) ?></span><a class="ghost" href="logout.php">Salir</a></div>
  <?php endif; ?>
</header>
