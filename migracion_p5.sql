
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

INSERT IGNORE INTO categorias (id, nombre) VALUES
    (1, 'Trabajo'),
    (2, 'Personal'),
    (3, 'Estudio'),
    (4, 'Ocio / Deporte');


DROP TABLE IF EXISTS eventos;

CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(120) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NULL,
    categoria_id INT NOT NULL,
    descripcion VARCHAR(500) NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_eventos_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON DELETE RESTRICT
);