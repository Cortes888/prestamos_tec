
-- 1. LIMPIEZA Y CREACIÓN DE LA BASE DE DATOS
DROP DATABASE IF EXISTS prestamos_tec;
CREATE DATABASE prestamos_tec CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prestamos_tec;

-- 2. TABLAS MAESTRAS
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

-- 3. TABLAS DE GESTIÓN
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
    -- ADR-003: vale id_objeto mientras el préstamo está abierto y NULL cuando se cierra.
    -- Con el índice único de abajo, la base no acepta dos préstamos abiertos del mismo equipo.
    -- Es VIRTUAL a propósito: id_objeto tiene borrado en cascada, y una columna STORED no lo permite.
    objeto_en_prestamo        INT GENERATED ALWAYS AS (IF(estado IN ('activo', 'vencido'), id_objeto, NULL)) VIRTUAL,
    UNIQUE KEY uq_prestamo_abierto_por_objeto (objeto_en_prestamo),
    -- Borrado en cascada, como en V2: al borrar un usuario o un equipo se borran sus préstamos.
    CONSTRAINT fk_prestamo_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_prestamo_objeto  FOREIGN KEY (id_objeto)  REFERENCES objeto(id_objeto)  ON DELETE CASCADE
);


USE prestamos_tec;
CREATE TABLE notificacion (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_prestamo     INT          NOT NULL,
    correo_destino  VARCHAR(150) NOT NULL,
    asunto          VARCHAR(200) NOT NULL,
    cuerpo          TEXT,
    enviado         TINYINT(1)   NOT NULL DEFAULT 0,
    fecha_envio     DATETIME,
    CONSTRAINT fk_notificacion_prestamo FOREIGN KEY (id_prestamo)
        REFERENCES prestamo(id_prestamo) ON DELETE CASCADE
);

-- 4. DATOS FIJOS (REQUERIDOS PARA QUE EL SISTEMA FUNCIONE)
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

-- 5. VISTA (igual que en V2)
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

-- 6. FUNCIONES (iguales que en V2; las usan usuarios.php y dashboard.php)
DELIMITER $$

CREATE FUNCTION fn_total_prestamos_activos(p_usuario INT) RETURNS INT
READS SQL DATA
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM prestamo WHERE id_usuario = p_usuario AND estado IN ('activo', 'vencido');
    RETURN total;
END$$

CREATE FUNCTION fn_dias_retraso(p_prestamo INT) RETURNS INT
READS SQL DATA
BEGIN
    DECLARE dias INT;
    SELECT DATEDIFF(CURDATE(), fecha_devolucion_esperada) INTO dias FROM prestamo WHERE id_prestamo = p_prestamo;
    IF dias < 0 THEN SET dias = 0; END IF;
    RETURN dias;
END$$

DELIMITER ;