USE facturas;

DELIMITER //
DROP PROCEDURE IF EXISTS insertar_cliente//
CREATE PROCEDURE insertar_cliente(IN p_cedula VARCHAR(50), IN p_nombre VARCHAR(50), IN p_apellidos VARCHAR(50), IN p_direccion VARCHAR(50), IN p_email VARCHAR(20), IN p_celular VARCHAR(11))
BEGIN
 INSERT INTO clientes(cedula,nombre,apellidos,direccion,email,celular) VALUES(p_cedula,p_nombre,p_apellidos,p_direccion,p_email,p_celular);
END//
DROP PROCEDURE IF EXISTS consultar_cliente//
CREATE PROCEDURE consultar_cliente(IN p_cedula VARCHAR(50))
BEGIN SELECT * FROM clientes WHERE cedula=p_cedula; END//
DROP PROCEDURE IF EXISTS mostrar_clientes//
CREATE PROCEDURE mostrar_clientes()
BEGIN SELECT * FROM clientes; END//
DROP PROCEDURE IF EXISTS actualizar_cliente//
CREATE PROCEDURE actualizar_cliente(IN p_cedula VARCHAR(50), IN p_nombre VARCHAR(50), IN p_apellidos VARCHAR(50), IN p_direccion VARCHAR(50), IN p_email VARCHAR(20), IN p_celular VARCHAR(11))
BEGIN UPDATE clientes SET nombre=p_nombre,apellidos=p_apellidos,direccion=p_direccion,email=p_email,celular=p_celular WHERE cedula=p_cedula; END//
DROP PROCEDURE IF EXISTS insertar_factura//
CREATE PROCEDURE insertar_factura(IN p_numero VARCHAR(50), IN p_cedula VARCHAR(50), IN p_fecha VARCHAR(50))
BEGIN INSERT INTO facturas(numero_factura,cedula,fecha) VALUES(p_numero,p_cedula,p_fecha); END//
DROP PROCEDURE IF EXISTS insertar_detalle_factura//
CREATE PROCEDURE insertar_detalle_factura(IN p_numero VARCHAR(50), IN p_codigo VARCHAR(50), IN p_cantidad INT)
BEGIN
 DECLARE v_precio VARCHAR(50);
 SELECT precio INTO v_precio FROM productos WHERE codigo=p_codigo;
 INSERT INTO detalle_factura(numero_factura,codigo_producto,cantidad,precio_unitario) VALUES(p_numero,p_codigo,p_cantidad,v_precio);
END//
DROP PROCEDURE IF EXISTS consultar_factura//
CREATE PROCEDURE consultar_factura(IN p_numero VARCHAR(50))
BEGIN
 SELECT d.codigo_producto AS codigo,p.nombre,d.cantidad,d.precio_unitario,(d.cantidad*d.precio_unitario) AS subtotal
 FROM detalle_factura d INNER JOIN productos p ON d.codigo_producto=p.codigo WHERE d.numero_factura=p_numero;
END//
DROP PROCEDURE IF EXISTS mostrar_facturas//
CREATE PROCEDURE mostrar_facturas()
BEGIN
 SELECT f.numero_factura,f.cedula,c.nombre,f.fecha,SUM(d.cantidad*d.precio_unitario) AS total
 FROM facturas f INNER JOIN clientes c ON f.cedula=c.cedula LEFT JOIN detalle_factura d ON f.numero_factura=d.numero_factura
 GROUP BY f.numero_factura,f.cedula,c.nombre,f.fecha ORDER BY f.numero_factura;
END//
DELIMITER ;

-- DATOS DE PRUEBA
INSERT INTO clientes VALUES ('1002003001','Carlos','Martinez','Calle 30 #12-45','carlos@gmail.com','3001112233');
INSERT INTO productos VALUES ('P001','Teclado inalambrico','85000'),('P002','Mouse optico','45000'),('P003','Memoria USB 64GB','32000'),('P004','Audifonos Bluetooth','120000'),('P005','Base para portatil','70000');
INSERT INTO facturas VALUES ('F001','1002003001','2026-09-03');
INSERT INTO detalle_factura VALUES ('F001','P001','1','85000'),('F001','P002','2','45000'),('F001','P003','1','32000'),('F001','P005','1','70000');
