CREATE DATABASE IF NOT EXISTS toolboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE toolboard;
CREATE TABLE usuarios(id INT AUTO_INCREMENT PRIMARY KEY, usuario VARCHAR(40) NOT NULL UNIQUE, nombre VARCHAR(80) NOT NULL, password_hash VARCHAR(255) NOT NULL, rol ENUM('admin','usuario') NOT NULL DEFAULT 'usuario', activo TINYINT(1) NOT NULL DEFAULT 1) ENGINE=InnoDB;
CREATE TABLE categorias(id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(60) NOT NULL UNIQUE, activo TINYINT(1) NOT NULL DEFAULT 1) ENGINE=InnoDB;
CREATE TABLE herramientas(id INT AUTO_INCREMENT PRIMARY KEY, codigo VARCHAR(30) NOT NULL UNIQUE, nombre VARCHAR(100) NOT NULL, categoria_id INT NULL, marca VARCHAR(60), descripcion TEXT, ubicacion VARCHAR(60),
 cantidad_total INT NOT NULL DEFAULT 0, cantidad_disponible INT NOT NULL DEFAULT 0, estado ENUM('activa','mantenimiento','fuera_servicio','baja') NOT NULL DEFAULT 'activa',
 CONSTRAINT ck_stock CHECK (cantidad_disponible>=0 AND cantidad_disponible<=cantidad_total), INDEX(estado), INDEX(nombre),
 CONSTRAINT fk_h_cat FOREIGN KEY(categoria_id) REFERENCES categorias(id)) ENGINE=InnoDB;
CREATE TABLE personal(id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(60) NOT NULL, apellido VARCHAR(60) NOT NULL, documento VARCHAR(30) NOT NULL UNIQUE, sector VARCHAR(60), cargo VARCHAR(60), activo TINYINT(1) NOT NULL DEFAULT 1) ENGINE=InnoDB;
CREATE TABLE prestamos(id INT AUTO_INCREMENT PRIMARY KEY, herramienta_id INT NOT NULL, personal_id INT NOT NULL, cantidad INT NOT NULL, cantidad_devuelta INT NOT NULL DEFAULT 0,
 fecha_prestamo DATE NOT NULL, fecha_prevista DATE NOT NULL, observaciones TEXT, estado ENUM('pendiente','parcial','devuelto') NOT NULL DEFAULT 'pendiente',
 CONSTRAINT ck_p CHECK (cantidad>0 AND cantidad_devuelta>=0 AND cantidad_devuelta<=cantidad), INDEX(estado), INDEX(fecha_prevista),
 CONSTRAINT fk_p_h FOREIGN KEY(herramienta_id) REFERENCES herramientas(id), CONSTRAINT fk_p_w FOREIGN KEY(personal_id) REFERENCES personal(id)) ENGINE=InnoDB;
CREATE TABLE devoluciones(id INT AUTO_INCREMENT PRIMARY KEY, prestamo_id INT NOT NULL, cantidad INT NOT NULL, danada INT NOT NULL DEFAULT 0, observaciones TEXT, fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_d_p FOREIGN KEY(prestamo_id) REFERENCES prestamos(id)) ENGINE=InnoDB;
CREATE TABLE mantenimientos(id INT AUTO_INCREMENT PRIMARY KEY, herramienta_id INT NOT NULL, problema VARCHAR(255) NOT NULL, responsable VARCHAR(80) NOT NULL, fecha_inicio DATE NOT NULL, fecha_fin DATE NULL,
 estado ENUM('en_proceso','finalizado') NOT NULL DEFAULT 'en_proceso', observaciones TEXT, INDEX(estado), CONSTRAINT fk_m_h FOREIGN KEY(herramienta_id) REFERENCES herramientas(id)) ENGINE=InnoDB;
CREATE TABLE historial(id INT AUTO_INCREMENT PRIMARY KEY, tipo VARCHAR(30) NOT NULL, descripcion VARCHAR(255) NOT NULL, herramienta_id INT NULL, usuario_id INT NULL, fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX(tipo),
 CONSTRAINT fk_hi_h FOREIGN KEY(herramienta_id) REFERENCES herramientas(id) ON DELETE SET NULL, CONSTRAINT fk_hi_u FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL) ENGINE=InnoDB;
-- Administrador inicial: admin / 123 (rol admin). Hash SHA-512 crypt, migrado a bcrypt en el primer login. Cambiar en producción.
INSERT IGNORE INTO usuarios(usuario,nombre,password_hash,rol) VALUES('admin','Administrador','$6$tbSalt2026xyz$rWyXu1U3td.GLiYcriU6j6rfGXVwnELPQUSO9QHhZuiNKFCWFnCVcJwgeU7lSYwqRhQCNtX9rc654eeU3FFso.','admin');
INSERT INTO categorias(nombre) VALUES('Herramientas manuales'),('Herramientas eléctricas'),('Medición'),('Seguridad');
INSERT INTO herramientas(codigo,nombre,categoria_id,marca,ubicacion,cantidad_total,cantidad_disponible,descripcion) VALUES
('HM-001','Llave inglesa 12"',1,'Stanley','Estante A1',6,6,'Ajustable'),('HM-002','Martillo de bola',1,'Truper','Estante A2',5,5,''),
('HE-001','Taladro percutor',2,'Bosch','Estante B1',3,3,'750 W'),('HE-002','Amoladora angular',2,'Makita','Estante B2',2,2,'4.5"'),
('ME-001','Calibre digital',3,'Mitutoyo','Vitrina C1',4,4,''),('SE-001','Casco de seguridad',4,'3M','Armario D1',10,10,'');
INSERT INTO personal(nombre,apellido,documento,sector,cargo) VALUES('Ana','Pérez','TB-1001','Soldadura','Operaria'),('Luis','Gómez','TB-1002','Mecanizado','Técnico'),
('Marta','Rojas','TB-1003','Mantenimiento','Supervisora'),('Jorge','Díaz','TB-1004','Montaje','Ayudante');
