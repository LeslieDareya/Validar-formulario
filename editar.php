<?php

require 'conexion.php';

// Obtener el ID del evento a editar
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

// Cargar categorías desde la base de datos para el <select>
$resultadoCat = $mysqli->query('SELECT id, nombre FROM categorias ORDER BY nombre');
$categorias = $resultadoCat->fetch_all(MYSQLI_ASSOC);
$idsValidos = array_map('intval', array_column($categorias, 'id'));

$errores = [];
$titulo = '';
$fecha = '';
$hora = '';
$categoriaId = 0;
$descripcion = '';

// Si es una petición POST (cuando guardan los cambios del formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');

    // Validación de Título
    if ($titulo === '') {
        $errores['titulo'] = 'El título es obligatorio.';
    } elseif (mb_strlen($titulo) > 120) {
        $errores['titulo'] = 'Máximo 120 caracteres.';
    }

    // Validación de Fecha
    if ($fecha === '') {
        $errores['fecha'] = 'La fecha es obligatoria.';
    } else {
        $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
            $errores['fecha'] = 'La fecha no es válida.';
        }
    }

    // Validación de Categoría
    if (!in_array($categoriaId, $idsValidos, true)) {
        $errores['categoria_id'] = 'Elige una categoría válida.';
    }

    // Validación de Descripción
    if (mb_strlen($descripcion) > 500) {
        $errores['descripcion'] = 'Máximo 500 caracteres.';
    }

    // ACTUALIZAR EN LA BASE DE DATOS
    if (empty($errores)) {
        $horaBD = ($hora !== '') ? $hora : null;
        $descripcionBD = ($descripcion !== '') ? $descripcion : null;

        $sql = "UPDATE eventos 
                SET titulo = ?, fecha = ?, hora = ?, categoria_id = ?, descripcion = ?
                WHERE id = ?";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssisi", $titulo, $fecha, $horaBD, $categoriaId, $descripcionBD, $id);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();

        header('Location: index.php?editado=1');
        exit;
    }

} else {
    // Si es GET, cargamos los datos actuales del evento desde la base de datos
    $sql = "SELECT titulo, fecha, hora, categoria_id, descripcion FROM eventos WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $evento = $resultado->fetch_assoc();

    if (!$evento) {
        header('Location: index.php');
        exit;
    }

    $titulo = $evento['titulo'];
    $fecha = $evento['fecha'];
    $hora = $evento['hora'] ?? '';
    $categoriaId = (int) $evento['categoria_id'];
    $descripcion = $evento['descripcion'] ?? '';

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgendaWeb | Editar evento</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilos.css?v=2">
</head>
<body>
    <header class="site-header">
        <div class="contenedor site-header__inner">
            <a href="index.php" class="logo">Agenda<span>Web</span></a>
            <nav class="nav">
                <a href="index.php" class="nav__link">Mis eventos</a>
                <a href="registrar.php" class="nav__link">Nuevo evento</a>
            </nav>
        </div>
    </header>

    <main class="contenedor">
        <section class="tarjeta">
            <div class="encabezado">
                <div>
                    <h1>Editar evento</h1>
                    <p>Modifica los datos de tu cita en AgendaWeb.</p>
                </div>
            </div>

            <form class="formulario" method="post" action="">
                <!-- TÍTULO -->
                <div class="campo">
                    <label for="titulo">Título del evento</label>
                    <input type="text" id="titulo" name="titulo" maxlength="120" value="<?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>" required>
                    <?php if (isset($errores['titulo'])): ?>
                        <small><?= htmlspecialchars($errores['titulo'], ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </div>

                <!-- FECHA Y HORA -->
                <div class="fila">
                    <div class="campo">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') ?>" required>
                        <?php if (isset($errores['fecha'])): ?>
                            <small><?= htmlspecialchars($errores['fecha'], ENT_QUOTES, 'UTF-8') ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="campo">
                        <label for="hora">Hora</label>
                        <input type="time" id="hora" name="hora" value="<?= htmlspecialchars($hora, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <!-- CATEGORÍA -->
                <div class="campo">
                    <label for="categoria_id">Categoría</label>
                    <select id="categoria_id" name="categoria_id" required>
                        <option value="">Selecciona una opción</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>" <?= $categoriaId === (int) $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['categoria_id'])): ?>
                        <small><?= htmlspecialchars($errores['categoria_id'], ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </div>

                <!-- DESCRIPCIÓN -->
                <div class="campo">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4" maxlength="500"><?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <?php if (isset($errores['descripcion'])): ?>
                        <small><?= htmlspecialchars($errores['descripcion'], ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </div>

                <!-- BOTONES -->
                <div class="acciones">
                    <a href="index.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="site-footer">
        <div class="contenedor">AgendaWeb · Leslie · 2026</div>
    </footer>
</body>
</html>