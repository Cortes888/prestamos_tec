-- 1. LIMPIEZA Y CREACIÓN DE BASE DE DATOS
DROP DATABASE IF EXISTS prestamos_tec;
CREATE DATABASE prestamos_tec CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prestamos_tec;

-- 2. TABLAS MAESTRAS (CON DATOS FIJOS)
CREATE TABLE facultad (
    id_facultad   INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    codigo        VARCHAR(20)  NOT NULL UNIQUE
);

CREATE TABLE categoria (
    id_categoria  INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(80)  NOT NULL,
    descripcion   VARCHAR(255)
);

-- 3. TABLAS DE GESTIÓN (PARA TUS CRUDS)
CREATE TABLE usuario (
    id_usuario    INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    apellido      VARCHAR(100) NOT NULL,
    correo        VARCHAR(150) NOT NULL UNIQUE,
    codigo_univ   VARCHAR(30)  NOT NULL UNIQUE,
    tipo          ENUM('estudiante','docente','administrativo') NOT NULL DEFAULT 'estudiante',
    id_facultad   INT,
    activo        TINYINT(1)   NOT NULL DEFAULT 1,
    fecha_registro DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_facultad FOREIGN KEY (id_facultad) REFERENCES facultad(id_facultad)
);

CREATE TABLE objeto (
    id_objeto     INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(120) NOT NULL,
    descripcion   VARCHAR(255),
    codigo_inv    VARCHAR(50)  NOT NULL UNIQUE,
    id_categoria  INT          NOT NULL,
    estado        ENUM('disponible','prestado','mantenimiento','baja') NOT NULL DEFAULT 'disponible',
    CONSTRAINT fk_objeto_categoria FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
);

CREATE TABLE prestamo (
    id_prestamo               INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario                INT      NOT NULL,
    id_objeto                 INT      NOT NULL,
    fecha_prestamo            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_devolucion_esperada DATETIME NOT NULL,
    fecha_devolucion_real     DATETIME,
    estado                    ENUM('activo','devuelto','vencido','perdido') NOT NULL DEFAULT 'activo',
    observaciones             TEXT,
    CONSTRAINT fk_prestamo_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    CONSTRAINT fk_prestamo_objeto  FOREIGN KEY (id_objeto)  REFERENCES objeto(id_objeto)
);

-- 4. INSERCIÓN DE DATOS FIJOS (REQUERIDOS PARA QUE EL SISTEMA FUNCIONE)
INSERT INTO facultad (nombre, codigo) VALUES
  ('Ingenierías y Arquitectura',        'UCLA1'),
  ('Educación y Humanidades',           'UCLA2'),
  ('Derecho y Ciencias Políticas',      'UCLA3'),
  ('Comunicación, Publicidad y Diseño', 'UCLA4');

INSERT INTO categoria (nombre, descripcion) VALUES
  ('Conectividad', 'Cables y adaptadores de señal'),
  ('Portátiles',   'Portátiles y equipos de cómputo'),
  ('Audio/Video',  'Bafles, micrófonos, proyectores y cámaras'),
  ('Energía',      'Regletas y cargadores');

-- 5. TRIGGERS PARA AUTOMATIZACIÓN (ESTADO DEL OBJETO)
DELIMITER $$

CREATE TRIGGER trg_prestamo_insert
AFTER INSERT ON prestamo
FOR EACH ROW
BEGIN
    IF NEW.estado = 'activo' THEN
        UPDATE objeto SET estado = 'prestado' WHERE id_objeto = NEW.id_objeto;
    END IF;
END$$

CREATE TRIGGER trg_prestamo_devolucion
AFTER UPDATE ON prestamo
FOR EACH ROW
BEGIN
    IF NEW.estado = 'devuelto' AND OLD.estado != 'devuelto' THEN
        UPDATE objeto SET estado = 'disponible' WHERE id_objeto = NEW.id_objeto;
    END IF;
END$$

DELIMITER ;

-- 6. VISTA PARA TU TABLA HTML (CRUD READ)
CREATE OR REPLACE VIEW v_prestamos_activos AS
SELECT
    p.id_prestamo,
    CONCAT(u.nombre, ' ', u.apellido) AS usuario_nombre,
    o.nombre AS objeto,
    p.fecha_prestamo,
    p.estado
FROM prestamo p
JOIN usuario u ON u.id_usuario = p.id_usuario
JOIN objeto o ON o.id_objeto = p.id_objeto;

USE prestamos_tec;

DELIMITER $$

-- 1. Restaurar la función para contar préstamos (Que usa usuarios.php)
CREATE FUNCTION fn_total_prestamos_activos(p_usuario INT) RETURNS INT
READS SQL DATA
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM prestamo WHERE id_usuario = p_usuario AND estado IN ('activo', 'vencido');
    RETURN total;
END$$

-- 2. Restaurar la función para los días de retraso (Que usa dashboard.php)
CREATE FUNCTION fn_dias_retraso(p_prestamo INT) RETURNS INT
READS SQL DATA
BEGIN
    DECLARE dias INT;
    SELECT DATEDIFF(CURDATE(), fecha_devolucion_esperada) INTO dias FROM prestamo WHERE id_prestamo = p_prestamo;
    IF dias < 0 THEN SET dias = 0; END IF;
    RETURN dias;
END$$

-- 3. Restaurar el procedimiento para crear préstamos (Que usa registrar_prestamo.php)
CREATE PROCEDURE sp_registrar_prestamo (
    IN  p_id_usuario  INT,
    IN  p_id_objeto   INT,
    IN  p_dias        INT,
    IN  p_obs         TEXT,
    OUT p_id_prestamo INT,
    OUT p_mensaje     VARCHAR(200)
)
BEGIN
    DECLARE v_estado_obj VARCHAR(20);
    SELECT estado INTO v_estado_obj FROM objeto WHERE id_objeto = p_id_objeto;
    
    IF v_estado_obj IS NULL THEN
        SET p_mensaje = 'ERROR: Objeto no existe.';
        SET p_id_prestamo = -1;
    ELSEIF v_estado_obj != 'disponible' THEN
        SET p_mensaje = CONCAT('ERROR: Objeto no disponible.');
        SET p_id_prestamo = -1;
    ELSE
        INSERT INTO prestamo (id_usuario, id_objeto, fecha_devolucion_esperada, observaciones)
        VALUES (p_id_usuario, p_id_objeto, NOW() + INTERVAL p_dias DAY, p_obs);
        SET p_id_prestamo = LAST_INSERT_ID();
        SET p_mensaje = 'OK: Préstamo registrado exitosamente.';
    END IF;
END$$

-- 4. Restaurar el procedimiento para devolver objetos (Que usa devolver.php)
CREATE PROCEDURE sp_registrar_devolucion (
    IN  p_id_prestamo INT,
    IN  p_obs         TEXT,
    OUT p_mensaje     VARCHAR(200)
)
BEGIN
    UPDATE prestamo
    SET estado = 'devuelto',
        fecha_devolucion_real = NOW(),
        observaciones = CONCAT(IFNULL(observaciones,''), ' | Devolución: ', p_obs)
    WHERE id_prestamo = p_id_prestamo AND estado != 'devuelto';
    SET p_mensaje = 'OK: Devolución registrada.';
END$$

DELIMITER ;

USE prestamos_tec;
-- Reniciador de bases de datos para poner las ids en 00
-- 1. Quitar y volver a poner la llave de Usuarios con borrado en cascada
ALTER TABLE prestamo DROP FOREIGN KEY fk_prestamo_usuario;
ALTER TABLE prestamo ADD CONSTRAINT fk_prestamo_usuario 
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) 
    ON DELETE CASCADE;

-- 2. Quitar y volver a poner la llave de Objetos con borrado en cascada
ALTER TABLE prestamo DROP FOREIGN KEY fk_prestamo_objeto;
ALTER TABLE prestamo ADD CONSTRAINT fk_prestamo_objeto 	
    FOREIGN KEY (id_objeto) REFERENCES objeto(id_objeto) 
    ON DELETE CASCADE;