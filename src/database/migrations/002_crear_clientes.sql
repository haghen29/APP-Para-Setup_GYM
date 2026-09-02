CREATE TABLE CLIENTE (
    id_cliente   INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_usuario   INT NOT NULL,
    empresa      VARCHAR(200),
    telefono     VARCHAR(30),
    direccion    VARCHAR(300)
);