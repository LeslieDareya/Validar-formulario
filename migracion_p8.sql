USE obedient_suit_gub_formulariodb;

-- Paso 1: Respaldo y tabla de usuarios
CREATE TABLE eventos_respaldo_p8 AS SELECT * FROM eventos;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  correo VARCHAR(120) NOT NULL UNIQUE,
  clave_hash VARCHAR(255) NOT NULL,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
);