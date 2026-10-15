-- 1. Insertar Usuarios
INSERT INTO usuario(nombre_completo, username, contrasena, email, estado) VALUES 
('Paco Sanchez Sanchez', 'dr.paco', '12345', 'paco.sanchez@consultorio.com', 'inactivo'),
('Gael Alvarado', 'adminPRO', 'admin123*', '253110012@utvam.edu.mx', 'activo'),
('Team de CExp', 'adminTEAM', '123456', 'ximena@gmail.com', 'inactivo');
-- 2. Insertar datos principales de el Médico
INSERT INTO medico (id_usuario, nombre, apellido_paterno, apellido_materno, telefono, cedula_profesional, especialidad)
VALUES (1,'Paco', 'Sanchez', 'Sanchez', '5500010101', '12345678','Medicina General');
-- 3. Insertar datos principales de los pacientes
INSERT INTO paciente (id_medico, nombre, apellido_paterno, apellido_materno, fecha_nacimiento, alergia, contacto)
VALUES 
(1, 'Gael Salvador','Alvarado','Villegas','2007-08-15','Sulfas','5512950452'),
(1, 'Alejandro','Garduno','Reyes','2007-01-01','Matematicas','12125412'),
(1, 'Dair','Castillo','','2005-12-12','Polvo','551100112'),
(1, 'Ximena', 'Flores', 'Soria', '2005-10-10', 'Higado Encebollado', '555-0152'),
(1, 'Joshua Shadahi', 'Belmontes', '', '2006-09-09', 'Ninguna', '523987321');
-- 4. Insertar datos principales de antecedentes
INSERT INTO antecedentes (id_paciente, notas) VALUES 
(1, 'Paciente con antecedentes alérgicos a la penicilina y asma leve controlada en la infancia.'),
(2, 'Cirugía de apendicectomía realizada en el año 2020 sin complicaciones.'),
(3, 'Dolor de estomago previo (gastritis confirmada'),
(4, 'Apendicitis, programacion de cirugia para 2027'),
(5, 'Ectoplasma');
-- 5. Insertar Grupos (Roles)
INSERT INTO grupo (id_grupo, nombre) VALUES 
(1, 'Administrador'),
(2, 'Medico'),
(3, 'Paciente');
-- 6. Insertar Permisos del sistema
INSERT INTO permiso (id_permiso, nombre, codename) VALUES 
(1, 'Crear Pacientes', 'add_patient'), -- Altas
(2, 'Editar Pacientes', 'change_patient'), -- Bajas
(3, 'Generar Consultas', 'add_consultation'), -- Consultas
(4, 'Emitir Recetas', 'add_prescription'); -- Cambios 
-- 7. Asignar el usuario al Grupo 'Medico' (Tabla 'contiene')
INSERT INTO contiene (id_usuario, id_grupo) VALUES 
(1, 2); -- El usuario 1 pertenece al grupo 2 (Medico)
-- 8. Asignar Permisos al Usuario (Tabla 'tiene')
INSERT INTO tiene (id_usuario, id_permiso) VALUES 
(1, 1), -- Puede crear pacientes 
(1, 2), -- Puede editar pacientes
(1, 3), -- Puede generar consultas
(1, 4); -- Puede emitir recetas
-- 9. Insertar Citas en la agenda virtual con diferentes estados
INSERT INTO cita (id_paciente, fecha, estado) VALUES 
(1, '2026-10-10 09:30:00', 'concluida'),
(2, '2026-10-12 11:00:00', 'concluida'),
(3, '2026-10-13 11:00:00', 'confirmada'),
(4, '2026-10-13 14:00:00', 'cancelada'),
(5, '2026-10-14 09:00:00', 'programada');