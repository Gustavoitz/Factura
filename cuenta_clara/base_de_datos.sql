-- Tablas y procedimientos de Cuenta. Ejecuta cada bloque por separado en defaultdb.
-- (Usan nombres propios: cuenta_clientes, cuenta_abonos, sp_cuenta_*, para no chocar con otras tablas.)

CREATE TABLE IF NOT EXISTS cuenta_clientes (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    usuario   VARCHAR(40)  NOT NULL UNIQUE,
    pwd_hash  VARCHAR(255) NOT NULL,
    nombres   VARCHAR(80)  NOT NULL,
    apellidos VARCHAR(80)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cuenta_abonos (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha      DATE NOT NULL,
    metodo     VARCHAR(30) NOT NULL,
    monto      DECIMAL(12,0) NOT NULL,
    INDEX idx_cuenta_abonos_cliente (cliente_id, fecha),
    CONSTRAINT fk_cuenta_abonos_cliente FOREIGN KEY (cliente_id)
        REFERENCES cuenta_clientes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP PROCEDURE IF EXISTS sp_cuenta_buscar_cliente;
CREATE PROCEDURE sp_cuenta_buscar_cliente(IN p_usuario VARCHAR(40))
BEGIN
    SELECT id, pwd_hash, nombres, apellidos
    FROM cuenta_clientes
    WHERE usuario = p_usuario;
END;

DROP PROCEDURE IF EXISTS sp_cuenta_registrar_cliente;
CREATE PROCEDURE sp_cuenta_registrar_cliente(
    IN p_usuario   VARCHAR(40),
    IN p_pwd_hash  VARCHAR(255),
    IN p_nombres   VARCHAR(80),
    IN p_apellidos VARCHAR(80)
)
BEGIN
    -- Si dos personas registran el mismo usuario a la vez, devuelve id 0
    DECLARE EXIT HANDLER FOR 1062 SELECT 0 AS id;

    IF EXISTS (SELECT 1 FROM cuenta_clientes WHERE usuario = p_usuario) THEN
        SELECT 0 AS id;                    -- el usuario ya existe
    ELSE
        INSERT INTO cuenta_clientes (usuario, pwd_hash, nombres, apellidos)
        VALUES (p_usuario, p_pwd_hash, p_nombres, p_apellidos);
        SELECT LAST_INSERT_ID() AS id;     -- id del cliente nuevo
    END IF;
END;

DROP PROCEDURE IF EXISTS sp_cuenta_listar_abonos;
CREATE PROCEDURE sp_cuenta_listar_abonos(IN p_cliente_id INT)
BEGIN
    SELECT fecha, metodo, monto
    FROM cuenta_abonos
    WHERE cliente_id = p_cliente_id
    ORDER BY fecha DESC, id DESC;
END;

DROP PROCEDURE IF EXISTS sp_cuenta_agregar_abono;
CREATE PROCEDURE sp_cuenta_agregar_abono(
    IN p_cliente_id INT,
    IN p_fecha      DATE,
    IN p_metodo     VARCHAR(30),
    IN p_monto      DECIMAL(12,0)
)
BEGIN
    INSERT INTO cuenta_abonos (cliente_id, fecha, metodo, monto)
    VALUES (p_cliente_id, p_fecha, p_metodo, p_monto);
END;

-- Ejemplo: dar un abono a un cliente (cambia el id)
-- CALL sp_cuenta_agregar_abono(1, '2026-10-20', 'Transferencia', 150000);
