-- 1. Insertar datos principales de el Médico
INSERT INTO medico (usuario, nombre_completo, telefono, cedula_profesional, especialidad)
VALUES ('dr.jorge.s@ejemplo.com', 'Dr. Jorge Salinas', '5500010101', '12345678','Medicina General');
-- 2. Insertar datos principales de los pacientes
INSERT INTO paciente (nombre, apellido_paterno, apellido_materno, fecha_nacimiento, alergia, contacto)
VALUES 
('Juan', 'Lopez', 'Lopez', '2001-12-25', 'Sulfametoxasol', '555444333'),
('Joshua', 'Flores', '', '2005-07-08', 'Ninguna', '52398732');
-- 3. Insertar datos principales de antecedentes
INSERT INTO antecedentes (id_paciente, notas)
VALUES ('1','Paciente presenta hipertension aterial.'), ('2','Paciente presenta asma desde nacimiento.');
-- 4. Insertar datos principales de la cita
INSERT INTO cita (id_paciente, id_medico, fecha, estado)
VALUES ('1', '1', '2026-09-27 13:00:00', 'confirmada'), ('2', '1', '2026-10-01 9:00:00', 'programada');