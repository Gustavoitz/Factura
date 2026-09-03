<?php
class cCliente
{
    function conectar()
    {
        $conexion = new mysqli(
    "gustavorm-gustavorm.i.aivencloud.com",
    "avnadmin",
    getenv('DB_PASSWORD'),
    "defaultdb",
    25926
);
        if ($conexion->connect_error) die("conexion fallida " . $conexion->connect_error);
        $conexion->set_charset("utf8");
        return $conexion;
    }

    function registrar_cliente($cedula,$nombre,$apellidos,$direccion,$email,$celular)
    {
        $conexion=$this->conectar();
        $sql="CALL insertar_cliente('$cedula','$nombre','$apellidos','$direccion','$email','$celular')";
        if(!$conexion->query($sql)) die("Error al insertar los datos: ".$conexion->error);
        $conexion->close();
        header('Location: index.php'); exit;
    }

    function consultar_cliente($cedula)
    {
        $conexion=$this->conectar();
        $resultado=$conexion->query("CALL consultar_cliente('$cedula')");
        if(!$resultado) die("Error en consulta: ".$conexion->error);
        $fila=$resultado->fetch_assoc();
        $resultado->free();
        $conexion->close();
        return $fila;
    }

    function mostrar_clientes()
    {
        $conexion=$this->conectar();
        $resultado=$conexion->query("CALL mostrar_clientes()");
        if(!$resultado) die("Error en consulta: ".$conexion->error);
        return array($conexion,$resultado);
    }

    function actualizar_cliente($cedula,$nombre,$apellidos,$direccion,$email,$celular)
    {
        $conexion=$this->conectar();
        $sql="CALL actualizar_cliente('$cedula','$nombre','$apellidos','$direccion','$email','$celular')";
        if(!$conexion->query($sql)) die("Error al actualizar: ".$conexion->error);
        $conexion->close();
        header('Location: index.php'); exit;
    }

    function registrar_factura($numero,$cedula,$fecha,$codigo1,$cantidad1,$codigo2,$cantidad2,$codigo3,$cantidad3,$codigo4,$cantidad4,$codigo5,$cantidad5)
    {
        $conexion=$this->conectar();
        $sql="CALL insertar_factura('$numero','$cedula','$fecha')";
        if(!$conexion->query($sql)) die("Error al registrar factura: ".$conexion->error);
        while($conexion->more_results()) { $conexion->next_result(); }
        $items=array(array($codigo1,$cantidad1),array($codigo2,$cantidad2),array($codigo3,$cantidad3),array($codigo4,$cantidad4),array($codigo5,$cantidad5));
        foreach($items as $item){
            if(trim($item[0])!=='' && (int)$item[1]>0){
                $codigo=$item[0]; $cantidad=(int)$item[1];
                $sql="CALL insertar_detalle_factura('$numero','$codigo',$cantidad)";
                if(!$conexion->query($sql)) die("Error al registrar producto: ".$conexion->error);
                while($conexion->more_results()) { $conexion->next_result(); }
            }
        }
        $conexion->close();
        header('Location: index.php'); exit;
    }
}
?>
