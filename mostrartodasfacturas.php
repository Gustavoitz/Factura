<?php $conexion = new mysqli(
    "gustavorm-gustavorm.i.aivencloud.com",
    "avnadmin",
    getenv('DB_PASSWORD'),
    "facturas",
    25926
); if($conexion->connect_error)die("Conexión fallida: ".$conexion->connect_error); $conexion->set_charset("utf8"); $result=$conexion->query("CALL mostrar_facturas()"); if(!$result)die("Error: ".$conexion->error); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /><title>Mostrar Facturas</title><link rel="stylesheet" href="css/estilos.css"></head><body><h1 align="center">FACTURAS REGISTRADAS</h1><table width="650" border="1" align="center"><thead><tr><th>Número Factura</th><th>Cédula</th><th>Nombre Cliente</th><th>Fecha</th><th>Total</th></tr></thead><tbody><?php while($row=$result->fetch_assoc()){ ?><tr><td><?php echo $row['numero_factura']; ?></td><td><?php echo $row['cedula']; ?></td><td><?php echo $row['nombre']; ?></td><td><?php echo $row['fecha']; ?></td><td>$<?php echo number_format($row['total'],2); ?></td></tr><?php } ?></tbody></table><br/><div align="center"><a href="index.php">Regresar al Menú</a></div></body></html>
