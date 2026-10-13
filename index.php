<?php

require 'conexion.php';

/* =====================================================
   FUNCIONES DE AYUDA
   ===================================================== */

function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function formatearFecha(string $fecha): string
{
    return date('d/m/Y', strtotime($fecha));
}

function mostrarEvento(array $ev): string
{
    $html = '<article class="card">';

    /* Categoría (viene del JOIN) */
    $html .= '<span class="card__badge">'
        . e($ev['categoria'])
        . '</span>';

    /* Título */
    $html .= '<h2 class="card__title">'
        . e($ev['titulo'])
        . '</h2>';

    /* Fecha */
    $cuando = formatearFecha($ev['fecha']);

    /* Hora opcional */
    if (!empty($ev['hora'])) {
        $cuando .= ' · ' . substr($ev['hora'], 0, 5);
    }

    $html .= '<p class="card__meta">';
    $html .= '<time datetime="' . e($ev['fecha']) . '">';
    $html .= e($cuando);
    $html .= '</time>';
    $html .= '</p>';

    /* Descripción opcional */
    if (!empty($ev['descripcion'])) {
        $html .= '<p class="card__text">'
            . e($ev['descripcion'])
            . '</p>';
    }

    $id = (int) $ev['id'];

    /* Botones */
    $html .= '<div class="card__actions">';
    $html .= '<a href="editar.php?id=' . $id . '" class="btn-secondary btn-sm">Editar</a>';

    $html .= '<form method="post" action="borrar.php" class="form-inline" onsubmit="return confirm(\'¿Seguro que quieres borrar este evento?\');">';
    $html .= '<input type="hidden" name="id" value="' . $id . '">';
    $html .= '<button type="submit" class="btn-danger btn-sm">Borrar</button>';
    $html .= '</form>';
    $html .= '</div>';

    return $html . '</article>';
}

/* =====================================================
   EVENTOS REALES DE MYSQL (CON JOIN)
   ===================================================== */

$eventos = [];

$resultado = $mysqli->query(
    "SELECT e.id, e.titulo, e.fecha, e.hora, e.descripcion,
            c.nombre AS categoria
     FROM eventos e
     JOIN categorias c ON c.id = e.categoria_id
     ORDER BY e.fecha ASC, e.hora ASC"
);

while ($evento = $resultado->fetch_assoc()) {
    $eventos[] = $evento;
}

$resultado->free();
$mysqli->close();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgendaWeb | Mis eventos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilos.css?v=5">
</head>
<body class="layout">
    <header class="site-header">
        <div class="contenedor site-header__inner">
            <a href="index.php" class="logo">Agenda<span>Web</span></a>
            <nav class="nav">
                <a href="index.php" class="nav__link is-active">Mis eventos</a>
                <a href="registrar.php" class="nav__link">Nuevo evento</a>
            </nav>
        </div>
    </header>

    <main class="contenedor">
        <?php if (isset($_GET['ok']) && $_GET['ok'] == '1'): ?>
            <div class="alert alert--ok" role="status">&#9989; Evento guardado correctamente.</div>
        <?php endif; ?>

        <?php if (isset($_GET['editado']) && $_GET['editado'] == '1'): ?>
            <div class="alert alert--ok" role="status">&#9989; Evento actualizado correctamente.</div>
        <?php endif; ?>

        <?php if (isset($_GET['borrado']) && $_GET['borrado'] == '1'): ?>
            <div class="alert alert--ok" role="status">&#9989; Evento borrado correctamente.</div>
        <?php endif; ?>

        <div class="page__header">
            <div>
                <h1 class="page__title">Mis eventos</h1>
                <p class="page__subtitle"><?= count($eventos) ?> eventos registrados</p>
            </div>
            <a href="registrar.php" class="btn-primary">+ Nuevo evento</a>
        </div>

        <?php if (!empty($eventos)): ?>
            <section class="card-list" aria-label="Lista de eventos">
                <?php foreach ($eventos as $ev): ?>
                    <?= mostrarEvento($ev) ?>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <div class="empty-state">
                <p>Aún no tienes eventos registrados.</p>
                <a href="registrar.php" class="btn-primary">Registrar el primero</a>
            </div>
        <?php endif; ?>
    </main>

    <footer class="site-footer">
        <div class="contenedor">AgendaWeb · Leslie · 2026</div>
    </footer>
</body>
</html>