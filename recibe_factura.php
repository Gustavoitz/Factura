<?php
include_once("Cservicios.php"); $obj=new cCliente;
$obj->registrar_factura($_POST['numero_factura'],$_POST['cedula'],$_POST['fecha'],$_POST['codigo1'],$_POST['cantidad1'],$_POST['codigo2'],$_POST['cantidad2'],$_POST['codigo3'],$_POST['cantidad3'],$_POST['codigo4'],$_POST['cantidad4'],$_POST['codigo5'],$_POST['cantidad5']);
?>
