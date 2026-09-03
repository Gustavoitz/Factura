<?php
$cedula=$_POST['cedula']; $nombre=$_POST['nombre']; $apellidos=$_POST['apellidos']; $direccion=$_POST['direccion']; $email=$_POST['email']; $celular=$_POST['celular'];
include_once("Cservicios.php");
$objconsulta=new cCliente;
$objconsulta->registrar_cliente($cedula,$nombre,$apellidos,$direccion,$email,$celular);
?>
