<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo ?? 'Cuenta') ?></title>
<link rel="icon" href="assets/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/estilos.css">
<script>try{if(sessionStorage.getItem('cc_loader'))document.documentElement.classList.add('sin-loader')}catch(e){}</script>
<noscript><style>header,main,.brand{opacity:1!important}#loader,#logo{display:none}</style></noscript>
</head>
<body>
<header>
  <a id="slot" href="index.php" aria-label="Inicio"></a>
  <span class="brand">Cuenta</span>
  <?php if (isset($_SESSION['modo'])): ?>
  <div id="who"><span><?= e($_SESSION['nombre']) ?></span><a class="ghost" href="logout.php">Salir</a></div>
  <?php endif; ?>
</header>
