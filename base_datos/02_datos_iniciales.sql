USE cexp_database;

-- 1. Insertar Grupos (Roles)
INSERT INTO grupo (id_grupo, nombre) VALUES 
(1, 'Administrador'),
(2, 'Medico'),
(3, 'Recepcionista');

-- 2. Permisos del sistema
INSERT INTO permiso (id_permiso, nombre, codename) VALUES
(1, 'Crear Pacientes', 'add_patient'),
(2, 'Editar Pacientes', 'change_patient'),
(3, 'Generar Consultas', 'add_consultation'),
(4, 'Emitir Recetas', 'add_prescription'),
(5, 'Ver Agenda', 'ver_agenda'),
(6, 'Asignar roles', 'add_roles'),
(7, 'Crear cita', 'add_date');
-- 3. Insertar Usuarios
INSERT INTO usuario(id_usuario, nombre_completo, username, contrasena, email, estado) VALUES 
(1, 'Admin Demo', 'adminDemo', '$2y$10$dkx1Zc3gQzr5fRdkznmMlOmRvHhZFMRpgk.Nf996dccYL.JK2ynSO', 'admin.demo@ejemplo.com', 'activo'),
(2, 'Ana Lopez Ramirez', 'dra.ana', '$2y$10$kkADXV.I67kFsltFb39wieOiJXKmofh9gvxkFSApF.aqF8uX5OYKK', 'ana.lopez@ejemplo.com', 'activo'),
(3, 'Mario Gutierrez Diaz', 'recep.mario', '$2y$10$JwVQdkQWiKi93alzhxBaoO5NoJL9wGJVGV95K0RUTeQyTEXbYYlQi', 'mario.gutierrez@ejemplo.com', 'activo'),
(4, 'Luis Herrera Mora', 'inactivo','$2y$10$35wBsk1u5HlOhUYDk3jcuOd0Tz4LFNaDp7WpJZ/P1Y.J5cDuThAZy', 'luis.herrera@ejemplo.com', 'inactivo');

-- 4. Asignar Usuarios a Grupos (Tabla 'contiene')
INSERT INTO contiene (id_usuario, id_grupo) VALUES 
(1, 1),  -- adminDemo -> Administrador
(2, 2),  -- dra.ana -> Medico
(3, 3),  -- recep.mario -> Recepcionista
(4, 2);  -- inactivo.demo -> Medico

-- 5. Permisos por grupo (tabla tiene)
INSERT INTO tiene (id_grupo, id_permiso) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7),  -- Administrador: todos
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 7),          -- Medico: todos
(3, 5);                                          -- Recepcionista: solo ver agenda

-- 6. Insertar datos principales de el Médico
INSERT INTO medico (id_medico, id_usuario, nombre, apellido_paterno, apellido_materno, telefono, cedula_profesional, especialidad)
VALUES 
(1, 2, 'Ana', 'Lopez', 'Ramirez', '5550011223', '87654321', 'Medicina General'),
(2, 4, 'Luis', 'Herrera', 'Mora', '5550099887', '11223344', 'Pediatria');

-- 7. Insertar datos principales de los pacientes
INSERT INTO paciente (id_paciente, id_medico, nombre, apellido_paterno, apellido_materno, fecha_nacimiento, alergia, contacto)
VALUES 
(1, 1, 'Sofia', 'Martinez', 'Vega', '1990-04-12', 'Penicilina', '5551112233'),
(2, 1, 'Jorge', 'Ruiz', 'Castillo', '1985-11-03', 'Ninguna', '5552223344'),
(3, 1, 'Valeria', 'Ortiz', '', '2010-06-21', 'Mariscos', '5553334455'),
(4, 1, 'Carlos', 'Navarro', 'Lopez', '1972-01-30', 'Sulfas', '5554445566'),
(5, 1, 'Daniela', 'Perez', 'Soto', '2002-09-15', 'Ninguna', '5555556677');

-- 8. Insertar datos principales de antecedentes
INSERT INTO antecedentes (id_antecedente, id_paciente, notas) VALUES
(1, 1, 'Alergia a la penicilina diagnosticada en 2015. Asma leve en la infancia.'),
(2, 2, 'Hipertension controlada desde 2018.'),
(3, 3, 'Gastritis confirmada en 2024.'),
(4, 4, 'Cirugia de rodilla en 2019 sin complicaciones.'),
(5, 5, 'Sin antecedentes relevantes.');

-- 9. Citas en la agenda ()
INSERT INTO cita (id_cita, id_paciente, fecha, estado) VALUES
(1, 1, '2026-10-10 09:30:00', 'concluida'),
(2, 2, '2026-10-12 11:00:00', 'concluida'),
(3, 3, '2026-10-13 11:00:00', 'confirmada'),
(4, 4, '2026-10-13 14:00:00', 'cancelada'),
(5, 5, '2026-10-14 09:00:00', 'programada'),
(6, 2, '2026-10-20 10:00:00', 'programada');

-- 10. Recetas
INSERT INTO receta (id_receta, diagnostico, fecha) VALUES
(1, 'Infeccion respiratoria leve', '2026-10-10 09:50:00'),
(2, 'Hipertension arterial, seguimiento', '2026-10-12 11:30:00'),
(3, 'Gastritis aguda', '2026-10-13 11:40:00');

-- 11. Detalle de cada receta
INSERT INTO receta_detalle (id_detalle, id_receta, medicamento, dosis, duracion, frecuencia) VALUES
(1, 1, 'Amoxicilina 500 mg', '1 capsula', '7 dias', 'Cada 8 horas'),
(2, 1, 'Paracetamol 500 mg', '1 tableta', '3 dias', 'Cada 6 horas si hay fiebre'),
(3, 2, 'Losartan 50 mg', '1 tableta', '30 dias', 'Una vez al dia'),
(4, 3, 'Omeprazol 20 mg', '1 capsula', '14 dias', 'Antes del desayuno');

-- 12. Consultas (signos vitales vinculados a paciente, receta y cita)
INSERT INTO consulta (id_consulta, id_paciente, id_receta, id_cita, fecha, temperatura, saturacion_oxigeno, frecuencia_respiratoria, pulso_cardiaco) VALUES
(1, 1, 1, 1, '2026-10-10 09:30:00', 38.20, 96, 18, 88),
(2, 2, 2, 2, '2026-10-12 11:00:00', 36.50, 98, 16, 76),
(3, 3, 3, 3, '2026-10-13 11:00:00', 36.80, 99, 17, 80),
(4, 2, 2, NULL, '2026-09-05 10:15:00', 36.60, 97, 16, 79);  -- consulta sin cita asociada