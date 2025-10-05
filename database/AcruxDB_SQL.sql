-- Crear base de datos (Se debe ejecutar antes que el resto, por separado)
DROP DATABASE db_acrux;
CREATE DATABASE db_acrux CHARACTER SET utf16 COLLATE utf16_spanish_ci;
USE db_acrux;


-- Creacion de Tablas
CREATE TABLE Persona (
    -- "INT UNSIGNED" permite que el rango de valores positivos de la variable se duplique, pero no podrá contener números negativos
    -- AUTO_INCREMENT incrementa en 1 con cada nuevo registro
    Id_persona INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(50) NOT NULL,
    Apellido VARCHAR(50) NOT NULL,
    -- "UNIQUE" hace que el atributo no pueda repetirse en la misma tabla
    DNI VARCHAR(10) UNIQUE NOT NULL,
    Email VARCHAR(255) UNIQUE NULL,
    Contrasena VARCHAR(255) NOT NULL
);


CREATE TABLE Funcionario (
    Id_funcionario INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Id_persona INT UNSIGNED NOT NULL
);


CREATE TABLE Telefono_Persona (
    Id_tel INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Telefono VARCHAR(20) UNIQUE NOT NULL,
    Id_persona INT UNSIGNED NOT NULL
);


CREATE TABLE Turno (
    Id_turno INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Turno ENUM('Diurno', 'Matutino', 'Vespertino', 'Nocturno') DEFAULT 'Diurno' NOT NULL
);


CREATE TABLE Turno_Funcionario (
    Id_funcionario INT UNSIGNED NOT NULL,
    Id_turno INT UNSIGNED NOT NULL
);


CREATE TABLE Turno_Grupo (
    Id_turno INT UNSIGNED NOT NULL,
    Id_grupo INT UNSIGNED NOT NULL
);


CREATE TABLE Materia (
    Id_materia INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(100) UNIQUE NOT NULL -- "Not Null" establece que el valor no puede ser nulo
);


CREATE TABLE Profesor (
    Id_profesor INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    FechaIngreso DATE NOT NULL,
    Id_funcionario INT UNSIGNED NOT NULL
);


CREATE TABLE Clase (
    Id_clase INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Id_profesor INT UNSIGNED NOT NULL,
    -- Es necesario que las claves foráneas sean del tipo "INT UNSIGNED" para ser compatibles con las claves primarias
    Id_materia INT UNSIGNED NOT NULL
);


CREATE TABLE Adscripta (
    Id_adscripta INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Id_funcionario INT UNSIGNED NOT NULL
);


CREATE TABLE ParteDiario (
    Id_entrada INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Accion VARCHAR(512) NOT NULL,
    Fecha_Hora TIMESTAMP NOT NULL,
    Id_adscripta INT UNSIGNED NULL
);


CREATE TABLE Noticia (
    Id_noticia INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Fecha_Hora TIMESTAMP NOT NULL,
    Contenido VARCHAR(512) NOT NULL,
    Id_adscripta INT UNSIGNED NULL
);


CREATE TABLE Etiqueta (
    Id_etiqueta INT UNSIGNED  PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(25) UNIQUE NOT NULL
);


CREATE TABLE Noticia_Etiqueta (
    Id_noticia INT UNSIGNED NOT NULL,
    Id_etiqueta INT UNSIGNED NOT NULL
);


CREATE TABLE Grupo (
    Id_grupo INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Codigo VARCHAR(15) UNIQUE NOT NULL,
    Id_adscripta INT UNSIGNED NOT NULL,
    Id_curso INT UNSIGNED NOT NULL
);


CREATE TABLE Curso (
    Id_curso INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(100) UNIQUE NOT NULL,
    DuracionAnios INT UNSIGNED NOT NULL
);


CREATE TABLE Materia_Curso (
    Id_materia INT UNSIGNED NOT NULL,
    Id_curso INT UNSIGNED NOT NULL
);


CREATE TABLE Estudiante (
    Id_estudiante INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Reputacion ENUM('BUENA', 'IMPUNTUAL', 'MALA') DEFAULT 'BUENA' NOT NULL,
    Id_grupo INT UNSIGNED NOT NULL,
    Id_persona INT UNSIGNED NOT NULL
);


CREATE TABLE Telefono_Tutor (
    Id_tel INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Telefono VARCHAR(20) UNIQUE NOT NULL,
    NombreTutor VARCHAR(100) NOT NULL,
    Id_estudiante INT UNSIGNED NOT NULL
);


CREATE TABLE Intervalo (
    Id_intervalo INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Numero INT NOT NULL UNIQUE,
    Entrada TIME NOT NULL,
    Salida TIME NOT NULL
);


CREATE TABLE Dia (
    Id_dia INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(20) UNIQUE NOT NULL
);


CREATE TABLE Hora (
    Id_hora INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Id_intervalo INT UNSIGNED NOT NULL,
    Id_dia INT UNSIGNED NOT NULL
);


CREATE TABLE Bloque (
    Id_bloque INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Id_grupo INT UNSIGNED NOT NULL,
    Id_clase INT UNSIGNED NOT NULL,
    Id_aula INT UNSIGNED NOT NULL,
    Id_hora INT UNSIGNED NOT NULL
);


CREATE TABLE Reserva (
    Id_reserva INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Id_hora INT UNSIGNED NOT NULL,
    Id_aula INT UNSIGNED NOT NULL,
    Id_funcionario INT UNSIGNED NOT NULL,
    Fecha DATE NOT NULL
);


CREATE TABLE Aula (
    Id_aula INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Codigo VARCHAR(10) UNIQUE NOT NULL,
    Piso VARCHAR(15) NOT NULL,
    Proposito VARCHAR(100) NOT NULL,
    CantidadSillas INT UNSIGNED NOT NULL
);


CREATE TABLE Computadora (
    Id_compu INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    SO VARCHAR(100) NOT NULL,
    -- "ENUM(valor1, valor2, etc)" permite limitar los valores de una columna a una lista concreta
    Estado ENUM('OK', 'MALFUNCIONAMIENTO', 'ROTO') DEFAULT 'OK' NOT NULL,
    Problema VARCHAR(512) NOT NULL DEFAULT 'Ninguno',
    Id_aula INT UNSIGNED NOT NULL
);


CREATE TABLE Software (
    Id_software INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(255) UNIQUE NOT NULL
);


CREATE TABLE Computadora_Software (
    Id_compu INT UNSIGNED NOT NULL,
    Id_software INT UNSIGNED NOT NULL
);


CREATE TABLE Secretario (
    Id_secretario INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Id_funcionario INT UNSIGNED NOT NULL
);

CREATE TABLE Administrador (
    Id_administrador INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Id_funcionario INT UNSIGNED NOT NULL
);


CREATE TABLE RecursoInterno (
    Id_recursoIn INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Tipo VARCHAR(50) NOT NULL,
    Estado ENUM('OK', 'MALFUNCIONAMIENTO', 'ROTO') DEFAULT 'OK' NOT NULL,
    Problema VARCHAR(512) NOT NULL DEFAULT 'Ninguno',
    Id_aula INT UNSIGNED NOT NULL
);


CREATE TABLE RecursoExterno (
    Id_recursoEx INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Tipo VARCHAR(50) NOT NULL,
    Disponible BOOLEAN NOT NULL DEFAULT TRUE,
    Id_aula INT UNSIGNED NOT NULL
);


CREATE TABLE RecExt_Estudiante (
    Id_registro INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Accion VARCHAR(50) NOT NULL,
    Fecha_Hora TIMESTAMP NOT NULL,
    Id_recursoEx INT UNSIGNED NOT NULL,
    Id_secretario INT UNSIGNED NULL,
    Id_estudiante INT UNSIGNED NOT NULL
);


CREATE TABLE RecExt_Funcionario (
    Id_registro INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Accion VARCHAR(50) NOT NULL,
    Fecha_Hora TIMESTAMP NOT NULL,
    Id_recursoEx INT UNSIGNED NOT NULL,
    Id_secretario INT UNSIGNED NULL,
    Id_funcionario INT UNSIGNED NOT NULL
);


CREATE TABLE Auxiliar (
    Id_auxiliar INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Id_funcionario INT UNSIGNED NOT NULL
);


CREATE TABLE Cargo (
    Id_cargo INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(100) UNIQUE NOT NULL
);


CREATE TABLE Auxiliar_Cargo (
    Id_auxiliar INT UNSIGNED NOT NULL,
    Id_cargo INT UNSIGNED NOT NULL
);


CREATE TABLE Aula_Auxiliar (
    Id_aula INT UNSIGNED NOT NULL,
    Id_auxiliar INT UNSIGNED NOT NULL
);


-- Definicion de las claves foraneas

-- En MySql es obligatorio que las claves foraneas tengan un nombre unico
-- Las nombres de las claves foraneas van a estar formadas por: "fk_(nombre tabla local)__(nombre tabla foranea)" 
-- Con doble guion bajo (__) entre los nombres de las tablas
ALTER TABLE Profesor ADD CONSTRAINT fk_profesor__funcionario FOREIGN KEY (Id_funcionario) REFERENCES Funcionario (Id_funcionario) ON DELETE CASCADE;

-- "ON DELETE CASCADE" hace que, cuando se elimine un registro en la tabla padre, automaticamente se eliminen los registros asociados en la tabla hija
-- En este caso, si se elimina el registro de un funcionario, tambien se eliminan todos los telefonos asociados
ALTER TABLE Clase ADD CONSTRAINT fk_clase__profesor FOREIGN KEY (Id_profesor) REFERENCES Profesor (Id_profesor) ON DELETE CASCADE;
ALTER TABLE Clase ADD CONSTRAINT fk_clase__materia FOREIGN KEY (Id_materia) REFERENCES Materia (Id_materia) ON DELETE CASCADE; 

ALTER TABLE Funcionario ADD CONSTRAINT fk_funcionario__persona FOREIGN KEY (Id_persona) REFERENCES Persona (Id_persona) ON DELETE CASCADE;

-- Nota: En el caso de los telefonos, la clave foranea se va a llamar como la tabla (despues de "fk_")
ALTER TABLE Telefono_Persona ADD CONSTRAINT fk_telefono_persona FOREIGN KEY (Id_persona) REFERENCES Persona (Id_persona) ON DELETE CASCADE;

ALTER TABLE Bloque ADD CONSTRAINT fk_bloque__grupo FOREIGN KEY (Id_grupo) REFERENCES Grupo (Id_grupo) ON DELETE CASCADE;
ALTER TABLE Bloque ADD CONSTRAINT fk_bloque__clase FOREIGN KEY (Id_clase) REFERENCES Clase (Id_clase);
ALTER TABLE Bloque ADD CONSTRAINT fk_bloque__aula FOREIGN KEY (Id_aula) REFERENCES Aula (Id_aula);
ALTER TABLE Bloque ADD CONSTRAINT fk_bloque__hora FOREIGN KEY (Id_hora) REFERENCES Hora (Id_hora) ON DELETE CASCADE;
-- Definir una restriccion que no permita que un grupo tenga mas de una clase o aula en la misma hora
ALTER TABLE Bloque ADD CONSTRAINT uq_aula__horario UNIQUE (id_aula, id_hora);
ALTER TABLE Bloque ADD CONSTRAINT uq_grupo__horario UNIQUE (id_grupo, id_hora);
-- Tampoco se le puede asignar una clase a varios grupos al mismo tiempo (porque, en teoria, un profesor no le puede dar clase a más de un grupo a la vez)
ALTER TABLE Bloque ADD CONSTRAINT uq_clase__horario UNIQUE (id_clase, id_hora);

ALTER TABLE Reserva ADD CONSTRAINT fk_reserva__hora FOREIGN KEY (Id_hora) REFERENCES Hora (Id_hora) ON DELETE CASCADE;
ALTER TABLE Reserva ADD CONSTRAINT fk_reserva__aula FOREIGN KEY (Id_aula) REFERENCES Aula (Id_aula) ON DELETE CASCADE;
ALTER TABLE Reserva ADD CONSTRAINT fk_reserva__funcionario FOREIGN KEY (Id_funcionario) REFERENCES Funcionario (Id_funcionario) ON DELETE CASCADE;

ALTER TABLE Hora ADD CONSTRAINT fk_hora__intervalo FOREIGN KEY (Id_intervalo) REFERENCES Intervalo (Id_intervalo) ON DELETE CASCADE;
ALTER TABLE Hora ADD CONSTRAINT fk_hora__dia FOREIGN KEY (Id_dia) REFERENCES Dia (Id_dia) ON DELETE CASCADE;

ALTER TABLE Grupo ADD CONSTRAINT fk_grupo__adscripta FOREIGN KEY (Id_adscripta) REFERENCES Adscripta (Id_adscripta);
ALTER TABLE Grupo ADD CONSTRAINT fk_grupo__curso FOREIGN KEY (Id_curso) REFERENCES Curso (Id_curso);

ALTER TABLE Materia_Curso ADD CONSTRAINT fk_materia_curso__materia FOREIGN KEY (Id_materia) REFERENCES Materia (Id_materia) ON DELETE CASCADE;
ALTER TABLE Materia_Curso ADD CONSTRAINT fk_materia_curso__curso FOREIGN KEY (Id_curso) REFERENCES Curso (Id_curso) ON DELETE CASCADE;

ALTER TABLE Estudiante ADD CONSTRAINT fk_estudiante__grupo FOREIGN KEY (Id_grupo) REFERENCES Grupo (Id_grupo);
ALTER TABLE Estudiante ADD CONSTRAINT fk_estudiante__persona FOREIGN KEY (Id_persona) REFERENCES Persona (Id_persona) ON DELETE CASCADE;

ALTER TABLE Telefono_Tutor ADD CONSTRAINT fk_telefono_tutor FOREIGN KEY (Id_estudiante) REFERENCES Estudiante (Id_estudiante) ON DELETE CASCADE;

ALTER TABLE Adscripta ADD CONSTRAINT fk_adscripta__funcionario FOREIGN KEY (Id_funcionario) REFERENCES Funcionario (Id_funcionario) ON DELETE CASCADE;

-- "ON DELETE SET NULL" hace que, cuando se elimine un registro en la tabla padre, automaticamente, en todas las tablas hijas que tengan una relación
-- con ese registro, cambien el valor de su columna FK correspondiente a null. Esto permite eliminar libremente los registros en la tabla padre, pero
-- sin borrar los registros en la tabla hija, lo que puede evitar problemas como eliminar registros de petición de recursos que no hayan sido devueltos
-- Si por algun motivo se elimina el registro de una adscripta, sus registros en el Parte Diario permaneceran
ALTER TABLE ParteDiario ADD CONSTRAINT fk_partediario__adscripta FOREIGN KEY (Id_adscripta) REFERENCES Adscripta (Id_adscripta) ON DELETE SET NULL;

ALTER TABLE Noticia ADD CONSTRAINT fk_noticia_adscripta FOREIGN KEY (Id_adscripta) REFERENCES Adscripta (Id_adscripta) ON DELETE SET NULL;

ALTER TABLE Noticia_Etiqueta ADD CONSTRAINT fk_noticia_etiqueta__noticia FOREIGN KEY (Id_noticia) REFERENCES Noticia (Id_noticia) ON DELETE CASCADE;
ALTER TABLE Noticia_Etiqueta ADD CONSTRAINT fk_noticia_etiqueta__etiqueta FOREIGN KEY (Id_etiqueta) REFERENCES Etiqueta (Id_etiqueta) ON DELETE CASCADE;

ALTER TABLE Computadora ADD CONSTRAINT fk_computadora__aula FOREIGN KEY (Id_aula) REFERENCES Aula (Id_aula);

ALTER TABLE Computadora_Software ADD CONSTRAINT fk_computadora_software__computadora FOREIGN KEY (Id_compu) REFERENCES Computadora (Id_compu) ON DELETE CASCADE;
ALTER TABLE Computadora_Software ADD CONSTRAINT fk_computadora_software__software FOREIGN KEY (Id_software) REFERENCES Software (Id_software);

ALTER TABLE RecursoInterno ADD CONSTRAINT fk_recursointerno__aula FOREIGN KEY (Id_aula) REFERENCES Aula (Id_aula);

ALTER TABLE RecursoExterno ADD CONSTRAINT fk_recursoexterno__aula FOREIGN KEY (Id_aula) REFERENCES Aula (Id_aula) ON DELETE CASCADE;

ALTER TABLE RecExt_Estudiante ADD CONSTRAINT fk_recext_estudiante__recursoexterno FOREIGN KEY (Id_recursoEx) REFERENCES RecursoExterno (Id_recursoEx) ON DELETE CASCADE;
ALTER TABLE RecExt_Estudiante ADD CONSTRAINT fk_recext_estudiante__secretario FOREIGN KEY (Id_secretario) REFERENCES Secretario (Id_secretario) ON DELETE SET NULL;
ALTER TABLE RecExt_Estudiante ADD CONSTRAINT fk_recext_estudiante__estudiante FOREIGN KEY (Id_estudiante) REFERENCES Estudiante (Id_estudiante) ON DELETE CASCADE;

ALTER TABLE RecExt_Funcionario ADD CONSTRAINT fk_recext_funcionario__recursoexterno FOREIGN KEY (Id_recursoEx) REFERENCES RecursoExterno (Id_recursoEx) ON DELETE CASCADE;
ALTER TABLE RecExt_Funcionario ADD CONSTRAINT fk_recext_funcionario__secretario FOREIGN KEY (Id_secretario) REFERENCES Secretario (Id_secretario) ON DELETE SET NULL;
ALTER TABLE RecExt_Funcionario ADD CONSTRAINT fk_recext_funcionario__funcionario FOREIGN KEY (Id_funcionario) REFERENCES Funcionario (Id_funcionario) ON DELETE CASCADE;

ALTER TABLE Secretario ADD CONSTRAINT fk_secretario__funcionario FOREIGN KEY (Id_funcionario) REFERENCES Funcionario (Id_funcionario) ON DELETE CASCADE;

ALTER TABLE Administrador ADD CONSTRAINT fk_administrador__funcionario FOREIGN KEY (Id_funcionario) REFERENCES Funcionario (Id_funcionario) ON DELETE CASCADE;

ALTER TABLE Turno_Funcionario ADD CONSTRAINT fk_turno_funcionario__funcionario FOREIGN KEY (Id_funcionario) REFERENCES Funcionario (Id_funcionario) ON DELETE CASCADE;
ALTER TABLE Turno_Funcionario ADD CONSTRAINT fk_turno_funcionario__turno FOREIGN KEY (Id_turno) REFERENCES Turno (Id_turno);

ALTER TABLE Turno_Grupo ADD CONSTRAINT fk_turno_grupo__grupo FOREIGN KEY (Id_grupo) REFERENCES Grupo (Id_grupo) ON DELETE CASCADE;
ALTER TABLE Turno_Grupo ADD CONSTRAINT fk_turno_grupo__turno FOREIGN KEY (Id_turno) REFERENCES Turno (Id_turno);

ALTER TABLE Auxiliar ADD CONSTRAINT fk_auxiliar__funcionario FOREIGN KEY (Id_funcionario) REFERENCES Funcionario (Id_funcionario) ON DELETE CASCADE;

ALTER TABLE Auxiliar_Cargo ADD CONSTRAINT fk_auxiliar_cargo__auxiliar FOREIGN KEY (Id_auxiliar) REFERENCES Auxiliar (Id_auxiliar) ON DELETE CASCADE;
ALTER TABLE Auxiliar_Cargo ADD CONSTRAINT fk_auxiliar_cargo__cargo FOREIGN KEY (Id_cargo) REFERENCES Cargo (Id_cargo);

-- Registros de Prueba
INSERT INTO Persona (Nombre, Apellido, DNI, Email, Contrasena)
VALUES
('Susana', 'Arbelo', '56473235', 'susanarbelo@gmail.com', '123'),
('Federico', 'Fagundez', '53748294', 'federicofagundez@gmail.com', '123'),
('Facundo', 'Rubil', '53759106','facundorubil@gmail.com', '123'),
('Ana', 'Inés', '57480926', 'anaines@gmil.com', '123'),
('Yanela', 'López', '57848372', 'yanelalopez@gmail.com', '123'),
('Paula', 'Fernandez', '54638076', 'paulafernandez@gmail.com', '123'),
('Roberto', 'Gutierrez', '65594733', 'robertogutierrez@gmail.com', '123'),
('Pancho', 'Mendoza', '65859476', NULL, '123'),
('Martina', 'Hernandez', '67394813', 'martinahernandez@gmail.com', '123'),
('Fernando', 'Root', '88888888', 'fernandoroot@gmail.com', '123'),
('Nehuel', 'Acosta', '11111111', 'nehuelacosta@gmail.com', '123'),
('Alejo', 'Bottesch', '22222222', 'alejobottesch@gmail.com', '123'),
('Michel', 'de Agustini', '44444444', 'micheldeagustini@gmail.com', '123'),
('Sofía', 'Verocai', '55555555', 'sofiaverocai@gmail.com', '123'),
('Emanuel', 'Gomez', '64648465', NULL, '123'),
('Sebastian', 'Menendez', '65486625', 'sebastianmenendez@gmail.com', '123'),
('Thiago', 'Díaz', '33333333', 'thiagodiaz@gmail.com', '123'),
('Bryan', 'Velara', '12637427', 'bryanvelara@gmail.com', '123');


INSERT INTO Materia (nombre)
VALUES
('Sociología'),
('Inglés Técnico II'),
('Programación Full Stack');


INSERT INTO Curso (nombre, DuracionAnios)
VALUES
('Informática Bilingüe', 3),
('Informática', 3);


INSERT INTO Materia_Curso (Id_materia, Id_curso)
VALUES
(1, 1),
(2, 1),
(3, 1),
(1, 2),
(2, 2),
(3, 2);


INSERT INTO Funcionario (Id_persona)
VALUES
(1),
(2),
(3),
(4),
(5),
(6),
(7),
(8),
(9),
(10);


INSERT INTO Profesor (FechaIngreso, Id_funcionario)
VALUES
('2020-05-20', 1),
('2020-02-17', 2),
('2023-07-01', 3);


INSERT INTO Clase (Id_profesor, Id_materia)
VALUES
(1, 1),
(2, 2),
(2, 3),
(3, 3);


INSERT INTO Telefono_Persona (telefono, Id_persona)
VALUES
('098567443', 1),
('095354772', 2),
('094367936', 3),
('094364993', 4),
('097484939', 5),
('096364853', 5),
('097674325', 11),
('094356482', 12),
('094564637', 13),
('094656564', 13),
('095463722', 15);


INSERT INTO Adscripta (Id_funcionario)
VALUES
(4),
(5);


INSERT INTO ParteDiario (Accion, Fecha_Hora, Id_adscripta)
VALUES
('El estudiante Thiago Diaz se retiró por dolor de cabeza. Su madre lo vino a buscar', '2025-07-31 9:30', 2),
-- En este caso, lo mejor seria usar CURRENT_TIMESTAMP para ingresar la fecha y hora del momento en que se hace el registro
('El estudiante Alejo Bottesch se retiró sin previo aviso. Se olvidó el celular', '2025-06-23 14:23', 2);


INSERT INTO Secretario (Id_funcionario)
VALUES
(6),
(7);


INSERT INTO Administrador (Id_funcionario)
VALUES
(10);


INSERT INTO Grupo (Codigo, Id_adscripta, Id_curso)
VALUES
('3ro MD', 2, 2),
('2do MR', 1, 1);


INSERT INTO Estudiante (Id_persona, Id_grupo)
VALUES
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 2),
(16, 2);


INSERT INTO Estudiante (reputacion, Id_persona, Id_grupo)
VALUES
('IMPUNTUAL', 17, 1),
('MALA', 18, 2);


INSERT INTO Telefono_Tutor (telefono, NombreTutor, Id_estudiante)
VALUES
('097346885', 'Fulano', 1),
('096472845', 'Mengano', 2);


INSERT INTO Noticia (Fecha_Hora, Contenido, Id_adscripta)
VALUES
('2025-06-10', '¡Empezó la temporada de la estufa 2025!', 1),
('2025-07-10', 'Comenzaron las inscripciones para los exámenes de Cambridge', 2);


INSERT INTO Etiqueta (Nombre)
VALUES
('IMPORTANTE'),
('Tiempo Limitado'),
('General'),
('ITSP'),
('Evento');


INSERT INTO Noticia_Etiqueta (Id_noticia, Id_etiqueta)
VALUES
(1, 3),
(1, 4),
(1, 5),
(2, 1),
(2, 2),
(2, 3),
(2, 5);


INSERT INTO Aula (Codigo, Piso, Proposito, CantidadSillas)
VALUES
('2B', 'PB', 'Informática', 25),
('1A', '1', 'Lab. Física', 20) ,
('3D', '2', 'General', 40);


-- Recursos sin problemas
INSERT INTO RecursoInterno (tipo, Id_aula)
VALUES
('Television', 1),
('Aire Acondicionado', 2);


-- Recursos con problemas (es necesario especificar que no funciona y cual es el problema)
INSERT INTO RecursoInterno (tipo, Estado, problema, Id_aula)
VALUES
('Ventilador', 'ROTO', 'Cable roto', 1), -- 'ROTO': Inutilizable
('Pizarrón (fibra)', 'MALFUNCIONAMIENTO', 'Se escribió con fibra permanente', 2); -- 'MALFUNCIONAMIENTO': Se puede usar, pero es incomodo o ineficiente


INSERT INTO Computadora (SO, Id_aula) -- Computadora sin problemas
VALUES
('Windows', 1),
('Windows', 1),
('Linux', 1);


INSERT INTO Computadora (SO, Estado, problema, Id_aula)
VALUES
('Windows', 'MALFUNCIONAMIENTO', 'La pantalla no puede mostrar el color azul', 1);


INSERT INTO Software (nombre)
VALUES
('NetBeans'),
('Visual Studio'),
('Vistual Box');


INSERT INTO Computadora_Software (Id_compu, Id_software)
VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 3),
(3, 2),
(4, 1),
(4, 3);


INSERT INTO RecursoExterno (tipo, Id_aula)
VALUES
('Control TV', 1),
('Llave I1', 1), -- I: Aula Informatica
('Control A/C', 2),
('Llave L1', 2), -- L: Laboratorio
('Llave G1', 3); -- G: Aula General


INSERT INTO RecExt_Estudiante (Accion, Fecha_Hora, Id_recursoEx, Id_secretario, Id_estudiante)
VALUES
('Devolver', '2025-06-29 12:15', 2, 2, 1);


INSERT INTO RecExt_Funcionario (Id_recursoEx, Id_funcionario, Fecha_Hora, Accion, Id_secretario)
VALUES
(1, 5, '2025-06-20 10:20', 'Retirar', 1),
(1, 5, '2025-06-20 10:30', 'Devolver', 2),
(4, 1, '2025-06-29 10:15', 'Retirar', 1),
(4, 1, '2025-06-29 10:50', 'Devolver', 1),
(2, 1, '2025-06-29 9:00', 'Retirar', 2),
(2, 1, '2025-06-29 12:15', 'Devolver', 2),
(2, 2, '2025-06-27 11:00', 'Retirar', 2),
(3, 2, '2025-06-27 11:00', 'Retirar', 2),
(2, 2, '2025-06-27 11:20', 'Devolver', 2),
(3, 2, '2025-06-27 11:20', 'Devolver', 2);


INSERT INTO Auxiliar (Id_funcionario)
VALUES
(8),
(9);


INSERT INTO Cargo (nombre)
VALUES
('Auxiliar de limpieza'),
('Técnico'),
('Ayudante de laboratorio');


INSERT INTO Auxiliar_Cargo (Id_auxiliar, Id_Cargo)
VALUES
(1, 1),
(1, 3),
(2, 1),
(2, 2),
(2, 3);


INSERT INTO Aula_Auxiliar (Id_aula, Id_auxiliar)
VALUES
(1, 1),
(1, 2),
(2, 1),
(3, 2);


INSERT INTO Turno (Turno)
VALUES
('Matutino'),
('Vespertino'),
('Nocturno');


INSERT INTO Turno_Funcionario (Id_funcionario, Id_turno)
VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 1),
(5, 2),
(6, 1),
(6, 2),
(7, 2),
(7, 3),
(8, 1),
(8, 2),
(8, 3),
(9, 1),
(9, 2);


INSERT INTO Turno_Grupo (Id_grupo, Id_turno)
VALUES
(1, 1),
(2, 1);

INSERT INTO Dia (Nombre)
VALUES
("Lunes"),
("Martes"),
("Miercoles"),
("Jueves"),
("Viernes"),
("Sabado"),
("Domingo");