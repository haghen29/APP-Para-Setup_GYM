CREATE TABLE SOLICITUD_SERVICIO (
    id_solicitud     INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_usuario       INT            NOT NULL,
    tipo_servicio    ENUM('desarrollo_web','ecommerce','sistema_gestion','app_administracion','mantenimiento','otro') NOT NULL,
    empresa          VARCHAR(200),
    telefono         VARCHAR(30),
    descripcion      TEXT           NOT NULL,
    fecha_solicitud  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitud_servicio_usuario FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario) ON DELETE CASCADE
);
