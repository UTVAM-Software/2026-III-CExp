CREATE TABLE paciente(
	id_paciente INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(20) NOT NULL,
	apellido_paterno VARCHAR(20) NOT NULL,
	apellido_materno VARCHAR(20),
	fecha_nacimiento DATE NOT NULL,
	alergia TEXT,
	contacto VARCHAR(100)
)ENGINE=InnoDB;
CREATE TABLE medico(
	id_medico INT AUTO_INCREMENT PRIMARY KEY,
	nombre_completo varchar(100) NOT NULL,
	usuario varchar(50) NOT NULL,
	telefono varchar(10) NOT NULL,
	cedula_profesional varchar(8) NOT NULL,
	especialidad text 
)ENGINE=InnoDB;
CREATE TABLE receta(
	id_receta INT AUTO_INCREMENT PRIMARY KEY,
	diagnostico text,
	fecha DATETIME DEFAULT CURRENT_TIMESTAMP
)ENGINE=InnoDB;
CREATE TABLE cita(
	id_cita INT AUTO_INCREMENT PRIMARY KEY,
	id_paciente int NOT NULL,
	id_medico int NOT NULL,
	fecha DATETIME NOT NULL,
	estado ENUM('cancelada','confirmada','concluida','programada') DEFAULT 'programada',
	CONSTRAINT fk_cita_paciente FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente) ON DELETE CASCADE,
	CONSTRAINT fk_cita_medico FOREIGN KEY (id_medico) REFERENCES medico(id_medico) ON DELETE CASCADE
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
	id_medico int NOT NULL,
	id_receta int NOT NULL,
    id_cita int NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
	temperatura decimal(4,2) NOT NULL,
	saturacion_oxigeno int NOT NULL,
	frecuencia_respiratoria int NOT NULL,
	pulso_cardiaco int NOT NULL,
	CONSTRAINT fk_paciente_consulta FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente) ON DELETE CASCADE,
	CONSTRAINT fk_medico_consulta FOREIGN KEY (id_medico) REFERENCES medico(id_medico) ON DELETE CASCADE,
	CONSTRAINT fk_receta_consulta FOREIGN KEY (id_receta) REFERENCES receta(id_receta) ON DELETE CASCADE,
    CONSTRAINT fk_cita_consulta FOREIGN KEY (id_cita) REFERENCES cita(id_cita) ON DELETE CASCADE
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