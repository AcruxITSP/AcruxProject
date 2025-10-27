DROP DATABASE IF EXISTS proyecto_itsp;
CREATE DATABASE proyecto_itsp CHARACTER SET utf16 COLLATE utf16_spanish_ci;
USE proyecto_itsp;

-- -- -- -- TABLAS - USUARIOS

-- Datos de usuario base, compartido entre usuarios especificos
DROP TABLE IF EXISTS Usuario;
CREATE TABLE Usuario (
    id_usuario INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    ci VARCHAR(8) UNIQUE NOT NULL,
    nombre VARCHAR(30) NOT NULL,
    apellido VARCHAR(30) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    email VARCHAR(50) NOT NULL
);

-- Usuario especifico, categorizacion de la tabla Usuario
DROP TABLE IF EXISTS Adscrito;
CREATE TABLE Adscrito (
    id_adscrito INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL
);

-- Usuario especifico, categorizacion de la tabla Usuario
DROP TABLE IF EXISTS Profesor;
CREATE TABLE Profesor (
    id_profesor INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS Ausencia;
CREATE TABLE Ausencia (
  id_ausencia INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_profesor INT UNSIGNED NOT NULL,
    motivo VARCHAR(512) NULL
);

DROP TABLE IF EXISTS IntervaloAusencia;
CREATE TABLE IntervaloAusencia (
  id_intervalo_ausencia INT UNSIGNED PRIMARY KEY NOT NULL,
  fecha DATE NOT NULL,
  id_periodo_inicio INT UNSIGNED NOT NULL,
  id_periodo_final INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS Ausencia_IntervaloAusencia;
CREATE TABLE Ausencia_IntervaloAusencia (
    id_ausencia INT UNSIGNED NOT NULL,
    id_intervalo_ausencia INT UNSIGNED NOT NULL
);

-- -- -- -- TABLAS - CLASES
DROP TABLE IF EXISTS Curso;
CREATE TABLE Curso (
  id_curso INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(80) UNIQUE NOT NULL
);

-- Informacion de una materia, Ejemplo: Programacion, Ciberseguridad
DROP TABLE IF EXISTS Materia;
CREATE TABLE Materia (
  id_materia INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(30) NOT NULL
);

DROP TABLE IF EXISTS Curso_Materia;
CREATE TABLE Curso_Materia (
    id_curso INT UNSIGNED NOT NULL,
    id_materia INT UNSIGNED NOT NULL
);

-- Esta tabla contiene la informacion que se uso para generar
-- los periodos proceduralmente
DROP TABLE IF EXISTS GeneracionPeriodos;
CREATE TABLE GeneracionPeriodos (
    entrada TIME NOT NULL,
    salida TIME NOT NULL,
    duracion_clase_minutos INT NOT NULL,
    duracion_recreo_minutos INT NOT NULL
);

-- Representa un lapso de tiempo en un dia dia cualquiera, Ejemplo: Primera, Segunda, etc.
DROP TABLE IF EXISTS Periodo;
CREATE TABLE Periodo (
    id_periodo INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    numero INT UNIQUE NOT NULL,
    entrada TIME NOT NULL,
    salida TIME NOT NULL
);

-- Representa un dia de la semana, Ejemplo: Lunes, Martes, Miércoles, etc.
-- "Default Lunes" no tiene un uso practico
DROP TABLE IF EXISTS Dia;
CREATE TABLE Dia (
    id_dia INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo') NOT NULL DEFAULT 'Lunes'
);

-- Representa un periodo relacionado a un dia, Ejemplo: Lunes a primera, Lunes a segunda, Martes a primera, etc.
DROP TABLE IF EXISTS Hora;
CREATE TABLE Hora (
    id_hora INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_periodo INT UNSIGNED NOT NULL,
    id_dia INT UNSIGNED NOT NULL
);

-- Representa la relaciones entre un Grupo y una Hora. Ejemplo: 3MD a Primera, 3MD a Segunda.
DROP TABLE IF EXISTS Modulo;
CREATE TABLE Modulo (
  id_modulo INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
      id_hora INT UNSIGNED NOT NULL,
    id_clase INT UNSIGNED NULL,
    id_espacio INT UNSIGNED NULL,
    id_grupo INT UNSIGNED NOT NULL
);

-- Representa un grupo. Ejemplo: 3MD
-- Se puede acceder a los valores en el ENUM al escribirlos tal cual, o a través de su índice (comenzando desde 1)
DROP TABLE IF EXISTS Grupo;
CREATE TABLE Grupo (
  id_grupo INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_adscrito INT UNSIGNED NULL, -- puede quedar NULL si se borra adscrito
    grado ENUM('1', '2', '3', '4') NOT NULL, -- Ejemplo: 1 - Primero, 2 - Segundo, 3 - Tercero
    nombre VARCHAR(5), -- Ejemplo: MD, MA, etc.
    id_curso INT UNSIGNED NULL
);

-- Representa la relacion de Profesor (Enseña) Materia
DROP TABLE IF EXISTS Clase;
CREATE TABLE Clase (
    id_clase INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_profesor INT UNSIGNED NULL, -- puede quedar NULL si se borra profesor
    id_materia INT UNSIGNED NOT NULL
);

-- -- -- -- TABLAS - ESPACIOS
-- Representa un espacio de la institucion, Ejemplo: Aula, Salon, Laboratorio de Quimica, Laboratorio de Fisica, etc.
DROP TABLE IF EXISTS Espacio;
CREATE TABLE Espacio (
    id_espacio INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    tipo ENUM('Salón', 'Aula', 'Lab. Física', 'Lab. Química', 'SUM', 'Zoom', 'Taller Manenimiento', 'Taller Electrónica', 'Taller', 'Laboratorio') NOT NULL DEFAULT 'Salon', -- Ejemplo: Id de la entrada 'Salon' o 'Aula'
    numero INT UNSIGNED NULL -- Ejemplo: 1 (para Aula 1, o Salon 1)
);

-- Contiene las informacion sobre que recurso interno tiene cada aula
DROP TABLE IF EXISTS Espacio_RecursoInterno;
CREATE TABLE Espacio_RecursoInterno (
    id_recurso_interno INT UNSIGNED NOT NULL,
    id_espacio INT UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL
);

-- Contiene informacion de la reserva de espacios creadas por profesores
DROP TABLE IF EXISTS ReservaEspacio;
CREATE TABLE ReservaEspacio (
    id_reserva INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NULL, -- puede quedar NULL si se borra profesor
    fecha DATE NOT NULL,
    id_espacio INT UNSIGNED NOT NULL,
    hora_final TIME NOT NULL
);

-- -- -- -- TABLAS - RECURSOS
-- Tabla base la cual contiene columnas compartidas para categorizar
DROP TABLE IF EXISTS Recurso;
CREATE TABLE Recurso (
    id_recurso INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    tipo VARCHAR(50) UNIQUE NOT NULL
);

DROP TABLE IF EXISTS RecursoExterno;
CREATE TABLE RecursoExterno (
    id_recurso_externo INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
  cantidad_total INT UNSIGNED NOT NULL,
    id_recurso INT UNSIGNED NOT NULL,
    id_espacio INT UNSIGNED NULL
);

DROP TABLE IF EXISTS RecursoInterno;
CREATE TABLE RecursoInterno (
    id_recurso_interno INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_recurso INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS ReservaRecurso;
CREATE TABLE ReservaRecurso (
    id_reserva INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    id_recurso_externo INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    cantidad INT NOT NULL,
    hora_final TIME NOT NULL
);

DROP TABLE IF EXISTS PeriodoReservaRecurso;
CREATE TABLE PeriodoReservaRecurso (
    id_reserva INT UNSIGNED NOT NULL,
    id_periodo INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS PeriodoReservaEspacio;
CREATE TABLE PeriodoReservaEspacio (
    id_reserva INT UNSIGNED NOT NULL,
    id_periodo INT UNSIGNED NOT NULL
);

-- -- -- -- RESTRICCIONES DE CLAVES FORANEAS

-- ============================
-- USUARIOS BASE
-- ============================

ALTER TABLE Adscrito
  ADD CONSTRAINT fk__adscrito_usuario FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE;

ALTER TABLE Profesor
  ADD CONSTRAINT fk__profesor_usuario FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE;

-- ============================
-- AUSENCIAS
-- ============================

ALTER TABLE Ausencia
  ADD CONSTRAINT fk__ausencia_profesor FOREIGN KEY (id_profesor) REFERENCES Profesor(id_profesor) ON DELETE CASCADE;

ALTER TABLE IntervaloAusencia
  ADD CONSTRAINT fk__intervaloAusencia_periodoInicio FOREIGN KEY (id_periodo_inicio) REFERENCES Periodo(id_periodo) ON DELETE CASCADE,
  ADD CONSTRAINT fk__intervaloAusencia_periodoFinal FOREIGN KEY (id_periodo_final) REFERENCES Periodo(id_periodo) ON DELETE CASCADE;

ALTER TABLE Ausencia_IntervaloAusencia
  ADD CONSTRAINT fk__ausencia_intervaloAusencia_ausencia FOREIGN KEY (id_ausencia) REFERENCES Ausencia(id_ausencia) ON DELETE CASCADE,
  ADD CONSTRAINT fk__ausencia_intervaloAusencia_intervaloAusencia FOREIGN KEY (id_intervalo_ausencia) REFERENCES IntervaloAusencia(id_intervalo_ausencia) ON DELETE CASCADE;

-- ============================
-- GRUPOS
-- ============================

ALTER TABLE Grupo
  ADD CONSTRAINT fk__grupo_adscrito FOREIGN KEY (id_adscrito) REFERENCES Adscrito(id_adscrito) ON DELETE SET NULL,
  ADD CONSTRAINT fk__grupo_curso FOREIGN KEY (id_curso) REFERENCES Curso(id_curso) ON DELETE SET NULL;

-- ============================
-- HORAS
-- ============================

ALTER TABLE Hora 
  ADD CONSTRAINT fk__hora_periodo FOREIGN KEY (id_periodo) REFERENCES Periodo(id_periodo) ON DELETE CASCADE,
  ADD CONSTRAINT fk__hora_dia FOREIGN KEY (id_dia) REFERENCES Dia(id_dia) ON DELETE CASCADE;

-- ============================
-- MODULOS (Relación Grupo ↔ Hora)
-- ============================

ALTER TABLE Modulo 
  ADD CONSTRAINT fk__modulos_hora FOREIGN KEY (id_hora) REFERENCES Hora(id_hora) ON DELETE CASCADE,
  ADD CONSTRAINT fk__modulos_clase FOREIGN KEY (id_clase) REFERENCES Clase(id_clase) ON DELETE SET NULL,
  ADD CONSTRAINT fk__modulos_espacio FOREIGN KEY (id_espacio) REFERENCES Espacio(id_espacio) ON DELETE SET NULL,
  ADD CONSTRAINT fk__modulos_grupo FOREIGN KEY (id_grupo) REFERENCES Grupo(id_grupo) ON DELETE CASCADE;

-- ============================
-- CLASES (Profesor ↔ Materia)
-- ============================

ALTER TABLE Clase
  ADD CONSTRAINT fk__clase_profesor FOREIGN KEY (id_profesor) REFERENCES Profesor(id_profesor) ON DELETE CASCADE,
  ADD CONSTRAINT fk__clase_materia FOREIGN KEY (id_materia) REFERENCES Materia(id_materia) ON DELETE CASCADE;

-- ============================
-- Curso ↔ Materia)
-- ============================

ALTER TABLE Curso_Materia
  ADD CONSTRAINT fk__curso_materia_curso FOREIGN KEY (id_curso) REFERENCES Curso(id_curso) ON DELETE CASCADE,
  ADD CONSTRAINT fk__curso_materia_materia FOREIGN KEY (id_materia) REFERENCES Materia(id_materia) ON DELETE CASCADE;

-- ============================
-- RESERVAS DE ESPACIOS
-- ============================

ALTER TABLE ReservaEspacio
  ADD CONSTRAINT fk__reservaEspacio_usuario FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
  ADD CONSTRAINT fk__reservaEspacio_espacio FOREIGN KEY (id_espacio) REFERENCES Espacio(id_espacio) ON DELETE CASCADE;

ALTER TABLE PeriodoReservaEspacio
  ADD CONSTRAINT fk__periodoReservaEspacio_reserva FOREIGN KEY (id_reserva) REFERENCES ReservaEspacio(id_reserva) ON DELETE CASCADE,
  ADD CONSTRAINT fk__periodoReservaEspacio_periodo FOREIGN KEY (id_periodo) REFERENCES Periodo(id_periodo) ON DELETE CASCADE;

ALTER TABLE Espacio_RecursoInterno
  ADD CONSTRAINT fk__espacio_recursoInterno_espacio FOREIGN KEY (id_espacio) REFERENCES Espacio(id_espacio) ON DELETE CASCADE,
  ADD CONSTRAINT fk__espacio_recursoInterno_recursoInterno FOREIGN KEY (id_recurso_interno) REFERENCES RecursoInterno(id_recurso_interno) ON DELETE CASCADE;

-- ============================
-- RECURSOS
-- ============================

ALTER TABLE RecursoExterno
  ADD CONSTRAINT fk__recursoExterno_recurso FOREIGN KEY (id_recurso) REFERENCES Recurso(id_recurso) ON DELETE CASCADE,
  ADD CONSTRAINT fk__recursoExterno_espacio FOREIGN KEY (id_espacio) REFERENCES Espacio(id_espacio) ON DELETE SET NULL;

ALTER TABLE RecursoInterno
  ADD CONSTRAINT fk__recursoInterno_recurso FOREIGN KEY (id_recurso) REFERENCES Recurso(id_recurso) ON DELETE CASCADE;

-- Agregar clave foránea hacia RecursosExternos
ALTER TABLE ReservaRecurso
  ADD CONSTRAINT fk__reservaRecurso_usuario FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
  ADD CONSTRAINT fk__reservaRecurso_recursoExterno FOREIGN KEY (id_recurso_externo) REFERENCES RecursoExterno(id_recurso_externo) ON DELETE CASCADE;

ALTER TABLE PeriodoReservaRecurso
  ADD CONSTRAINT fk__periodoReservaRecurso_reserva FOREIGN KEY (id_reserva) REFERENCES ReservaRecurso(id_reserva) ON DELETE CASCADE,
  ADD CONSTRAINT fk__periodoReservaRecurso_periodo FOREIGN KEY (id_periodo) REFERENCES Periodo(id_periodo) ON DELETE CASCADE;

-- -- -- -- DATOS
INSERT INTO Usuario (id_usuario, ci, nombre, apellido, contrasena, email) VALUES
(1, "11111111", "Profesor", "A", "admin", "adminprofe@gmail.com"),
(2, "22222222", "ADMIN", "ADS", "admin", "adminads@gmail.com"),
(3, "33333333", "Profesor", "B", "admin", "adminprofe2@gmail.com");

INSERT INTO Profesor (id_usuario) VALUES (1), (3);

INSERT INTO Adscrito (id_usuario) VALUES (2);

INSERT INTO GeneracionPeriodos(entrada, salida, duracion_clase_minutos, duracion_recreo_minutos)
VALUES ('07:00:00', '23:59:00', 45, 5);

INSERT INTO Periodo (id_periodo, numero, entrada, salida) VALUES
(1, 1, '07:00:00', '07:45:00'),
(2, 2, '07:50:00', '08:35:00'),
(3, 3, '08:40:00', '09:25:00'),
(4, 4, '09:30:00', '10:15:00'),
(5, 5, '10:20:00', '11:05:00');

INSERT INTO Recurso (`id_recurso`, `tipo`) VALUES 
(1, 'HDMI'),
(2, 'Alargue');

INSERT INTO RecursoExterno (`cantidad_total`, `id_recurso_externo`, `id_recurso`) VALUES 
('5', 1, '1'),
('8', 2, '2');

INSERT INTO ReservaRecurso (id_reserva, id_usuario, id_recurso_externo, fecha, cantidad, hora_final) VALUES
(1, 1, 1, '2025-10-11', 3, '11:05:00'),
(2, 1, 1, '2025-10-11', 2, '09:25:00'),
(3, 2, 1, '2025-10-11', 1, '11:05:00');
INSERT INTO PeriodoReservaRecurso (id_reserva, id_periodo) VALUES
(1, 1),
(1, 3),
(1, 4),
(2, 3),
(3, 5);

INSERT INTO Espacio (id_espacio, tipo, numero) VALUES
(1, "Salon", 5),
(2, "Aula", 1);

INSERT INTO Grupo (id_adscrito, grado, nombre) VALUES
(1, 3, 'MD'),
(1, 1, 'BE');

INSERT INTO Materia (id_materia, nombre) VALUES
(1, "Programacion"),
(2, "Ciberseguridad"),
(3, "Ingles");

INSERT INTO Dia (id_dia, nombre) VALUES
(1, 'Lunes'),
(2, 'Martes'),
(3, 'Miercoles'),
(4, 'Jueves'),
(5, 'Viernes');

INSERT INTO Hora (id_hora, id_dia, id_periodo) VALUES
( 1, 1, 1),
( 2, 1, 2),
( 3, 1, 3),
( 4, 1, 4),
( 5, 1, 5),

( 6, 2, 1),
( 7, 2, 2),
( 8, 2, 3),
( 9, 2, 4),
(10, 2, 5),

(11, 3, 1),
(12, 3, 2),
(13, 3, 3),
(14, 3, 4),
(15, 3, 5),

(16, 4, 1),
(17, 4, 2),
(18, 4, 3),
(19, 4, 4),
(20, 4, 5),

(21, 5, 1),
(22, 5, 2),
(23, 5, 3),
(24, 5, 4),
(25, 5, 5);

INSERT INTO Clase (id_clase, id_profesor, id_materia) VALUES
(1, 1, 1), -- PROFE: Programacion
(2, 1, 2), -- PROFE: Ciberseguridad
(3, 2, 3); -- PROFE2: Ingles

INSERT INTO Modulo (id_modulo, id_hora, id_clase, id_espacio, id_grupo) VALUES
(1, 2, 2, 2, 1), -- Lunes 2da - PROFE: Ciberseguridad - Aula 1 - 3MD,
(2, 3, 2, 2, 1), -- Lunes 3ra - PROFE: Ciberseguridad - Aula 1 - 3MD,
(3, 4, 1, 1, 1), -- Lunes 4ta - PROFE: Programacion - Salon 5 - 3MD,
(4, 5, 3, 1, 1), -- Lunes 5ta - PROFE: Ingles - Salon 5 - 3MD

(5, 8, 1, 1, 1), -- Martes 4ta - PROFE: Programacion - Salon 5 - 3MD,
(6, 9, 3, 1, 1); -- Martes 5ta - PROFE: Ingles - Salon 5 - 3MD