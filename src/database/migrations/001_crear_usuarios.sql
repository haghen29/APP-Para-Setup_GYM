CREATE TABLE USUARIO (
    id_usuario       INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre           VARCHAR(100)   NOT NULL,
    apellido         VARCHAR(100)   NOT NULL,
    email            VARCHAR(150)   NOT NULL UNIQUE,
    contrasena       VARCHAR(255)   NOT NULL,
    rol              ENUM('cliente','empleado','admin') NOT NULL DEFAULT 'cliente',
    fecha_registro   DATE           NOT NULL DEFAULT (CURRENT_DATE)
);
