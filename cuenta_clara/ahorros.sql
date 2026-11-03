-- Ahorros: base de datos banco_ahorros con UNA sola tabla (ahorros: id, saldo, ahorro).
-- Es independiente de defaultdb (donde siguen las tablas del banco y de facturación).
-- Ejecuta cada bloque por separado (selecciónalo completo y ejecútalo), de arriba hacia abajo.

-- 1) Base de datos
CREATE DATABASE IF NOT EXISTS banco_ahorros CHARACTER SET utf8mb4;

-- 2) Tabla
CREATE TABLE IF NOT EXISTS banco_ahorros.ahorros (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    saldo  DECIMAL(12,0) NOT NULL DEFAULT 0,
    ahorro DECIMAL(12,0) NOT NULL DEFAULT 0
);

-- 3) Clientes de ejemplo (el cliente 3 no tiene saldo, para mostrar el aviso)
INSERT INTO banco_ahorros.ahorros (id, saldo, ahorro) VALUES
    (1, 500000, 0),
    (2, 1200000, 150000),
    (3, 0, 80000),
    (4, 250000, 40000),
    (5, 75000, 0);

-- 4) Procedimientos (cada uno es un bloque aparte: primero el DROP y después el CREATE)

DROP PROCEDURE IF EXISTS banco_ahorros.sp_ahorros_listar;
CREATE PROCEDURE banco_ahorros.sp_ahorros_listar()
BEGIN
    SELECT id, saldo, ahorro FROM ahorros ORDER BY id;
END;

DROP PROCEDURE IF EXISTS banco_ahorros.sp_ahorros_consultar;
CREATE PROCEDURE banco_ahorros.sp_ahorros_consultar(IN p_id INT)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM ahorros WHERE id = p_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No existe un cliente con ese id';
    END IF;
    SELECT id, saldo, ahorro FROM ahorros WHERE id = p_id;
END;

-- Resta el valor del saldo y lo suma al ahorro, todo en una sola operación.
DROP PROCEDURE IF EXISTS banco_ahorros.sp_ahorrar;
CREATE PROCEDURE banco_ahorros.sp_ahorrar(IN p_id INT, IN p_valor DECIMAL(12,0))
BEGIN
    DECLARE v_saldo DECIMAL(12,0) DEFAULT NULL;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;

    IF p_valor IS NULL OR p_valor <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El valor debe ser mayor que cero';
    END IF;

    START TRANSACTION;
    SELECT saldo INTO v_saldo FROM ahorros WHERE id = p_id FOR UPDATE;

    IF v_saldo IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No existe un cliente con ese id';
    ELSEIF v_saldo = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El cliente no tiene saldo disponible';
    ELSEIF p_valor > v_saldo THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Saldo insuficiente para ese valor';
    END IF;

    UPDATE ahorros SET saldo = saldo - p_valor, ahorro = ahorro + p_valor WHERE id = p_id;
    COMMIT;

    SELECT id, saldo, ahorro FROM ahorros WHERE id = p_id;
END;

-- Ejemplo: pasar $50.000 del saldo al ahorro del cliente 1
-- CALL banco_ahorros.sp_ahorrar(1, 50000);
