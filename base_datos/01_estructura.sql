CREATE DATABASE IF NOT EXISTS cexp_database;
USE cexp_database;
CREATE TABLE grupo(
	id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(15) UNIQUE NOT NULL
)ENGINE=InnoDB;
CREATE TABLE permiso(
	id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) UNIQUE NOT NULL,
    codename VARCHAR(20) NOT NULL
)ENGINE=InnoDB;
CREATE TABLE usuario(
	id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(60) NOT NULL,
    username VARCHAR(30) NOT NULL UNIQUE,
    contrasena VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'inactivo'
)ENGINE=InnoDB;
CREATE TABLE contiene(
	id_usuario_grupo INT auto_increment PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_grupo INT NOT NULL,
    CONSTRAINT fk_usuario_contiene_grupo FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_grupo_contiene_usuario FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo) ON DELETE CASCADE
)ENGINE=InnoDB;
CREATE TABLE tiene(
	id_permiso_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_permiso INT NOT NULL,
    CONSTRAINT fk_usuario_tiene_permiso FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_permiso_tiene_usuario FOREIGN KEY (id_permiso) REFERENCES permiso(id_permiso) ON DELETE CASCADE
)ENGINE=InnoDB;
CREATE TABLE medico(
	id_medico INT auto_increment PRIMARY KEY,
    id_usuario INT UNIQUE NOT NULL,
    nombre VARCHAR(20) NOT NULL,
	apellido_paterno VARCHAR(20) NOT NULL,
	apellido_materno VARCHAR(20),
    telefono VARCHAR(12) NOT NULL,
    cedula_profesional VARCHAR(8) NOT NULL,
    especialidad TEXT,
    CONSTRAINT fk_usuario_medico FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
)ENGINE=InnoDB;
CREATE TABLE paciente(
	id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    id_medico INT NOT NULL,
	nombre VARCHAR(20) NOT NULL,
	apellido_paterno VARCHAR(20) NOT NULL,
	apellido_materno VARCHAR(20),
	fecha_nacimiento DATE NOT NULL,
	alergia TEXT,
	contacto VARCHAR(100),
    CONSTRAINT fk_medico_paciente FOREIGN KEY (id_medico) REFERENCES medico(id_medico) ON DELETE CASCADE
)ENGINE=InnoDB;
CREATE TABLE receta(
	id_receta INT AUTO_INCREMENT PRIMARY KEY,
	diagnostico text,
	fecha DATETIME DEFAULT CURRENT_TIMESTAMP
)ENGINE=InnoDB;
CREATE TABLE receta_detalle(
	id_detalle INT AUTO_INCREMENT PRIMARY KEY,
	id_receta int NOT NULL,
	medicamento varchar(100) NOT NULL,
	dosis varchar(50) NOT NULL,
	duracion varchar(50) NOT NULL,
	frecuencia varchar(50) NOT NULL,
	CONSTRAINT fk_detalle_receta FOREIGN KEY (id_receta) REFERENCES receta(id_receta) ON DELETE CASCADE
)ENGINE=InnoDB;
CREATE TABLE cita(
	id_cita INT AUTO_INCREMENT PRIMARY KEY,
	id_paciente int NOT NULL,
	fecha DATETIME NOT NULL,
	estado ENUM('cancelada','confirmada','concluida','programada') DEFAULT 'programada',
	CONSTRAINT fk_paciente_cita FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente) ON DELETE CASCADE
)ENGINE=InnoDB;
CREATE TABLE antecedentes(
	id_antecedente INT AUTO_INCREMENT PRIMARY KEY,
	id_paciente int NOT NULL,
	notas TEXT NOT NULL,
	CONSTRAINT fk_paciente_antecedente FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente) ON DELETE CASCADE
)ENGINE=InnoDB;
CREATE TABLE consulta(
	id_consulta INT AUTO_INCREMENT PRIMARY KEY,
	id_paciente int NOT NULL,
	id_receta int NOT NULL,
    id_cita int NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
	temperatura decimal(4,2) NOT NULL,
	saturacion_oxigeno int NOT NULL,
	frecuencia_respiratoria int NOT NULL,
	pulso_cardiaco int NOT NULL,
	CONSTRAINT fk_paciente_consulta FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente) ON DELETE CASCADE,
	CONSTRAINT fk_receta_consulta FOREIGN KEY (id_receta) REFERENCES receta(id_receta) ON DELETE CASCADE,
    CONSTRAINT fk_cita_consulta FOREIGN KEY (id_cita) REFERENCES cita(id_cita) ON DELETE CASCADE
)ENGINE=InnoDB;