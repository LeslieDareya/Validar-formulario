<?php
require __DIR__ . '/partes/sesion.php';
require 'conexion.php';

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
    $html .= '<span class="card__badge">' . e($ev['categoria']) . '</span>';
    $html .= '<h2 class="card__title">' . e($ev['titulo']) . '</h2>';

    $cuando = formatearFecha($ev['fecha']);
    if (!empty($ev['hora'])) {
        $cuando .= ' · ' . substr($ev['hora'], 0, 5);
    }

    $html .= '<p class="card__meta">';
    $html .= '<time datetime="' . e($ev['fecha']) . '">';
    $html .= e($cuando);
    $html .= '</time>';
    $html .= '</p>';

    if (!empty($ev['descripcion'])) {
        $html .= '<p class="card__text">' . e($ev['descripcion']) . '</p>';
    }

    $id = (int) $ev['id'];

    $html .= '<div class="card__actions">';
    $html .= '<a href="editar.php?id=' . $id . '" class="btn-secondary btn-sm">Editar</a>';
    $html .= '<form method="post" action="borrar.php" class="form-inline" onsubmit="return confirm(\'¿Seguro que quieres borrar este evento?\');">';
    $html .= '<input type="hidden" name="id" value="' . $id . '">';
    $html .= '<button type="submit" class="btn-danger btn-sm">Borrar</button>';
    $html .= '</form>';
    $html .= '</div>';

    return $html . '</article>';
}

$q = mb_substr(trim($_GET['q'] ?? ''), 0, 60);
$categoriaId = (int) ($_GET['categoria'] ?? 0);

$categorias = $mysqli->query('SELECT id, nombre FROM categorias ORDER BY nombre')->fetch_all(MYSQLI_ASSOC);
$idsValidos = array_map('intval', array_column($categorias, 'id'));
if (!in_array($categoriaId, $idsValidos, true)) {
    $categoriaId = 0;
}
$buscando = ($q !== '' || $categoriaId !== 0);

$condiciones = ['e.usuario_id = ?'];
$tipos       = 'i';
$valores     = [$usuarioId];

if ($q !== '') {
    $patron = '%' . addcslashes($q, '%_\\') . '%';
    $condiciones[] = '(e.titulo LIKE ? OR e.descripcion LIKE ?)';
    $tipos      .= 'ss';
    $valores[]   = $patron;
    $valores[]   = $patron;
}
if ($categoriaId !== 0) {
    $condiciones[] = 'e.categoria_id = ?';
    $tipos      .= 'i';
    $valores[]   = $categoriaId;
}

$sql = 'SELECT e.id, e.titulo, e.fecha, e.hora, e.descripcion,
              c.nombre AS categoria
          FROM eventos e
          JOIN categorias c ON c.id = e.categoria_id
         WHERE ' . implode(' AND ', $condiciones) . '
         ORDER BY e.fecha ASC, e.hora ASC';
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($tipos, ...$valores);
$stmt->execute();
$eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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
    <link rel="stylesheet" href="estilos.css?v=6">
</head>
<body class="layout">
    <header class="site-header">
        <div class="contenedor site-header__inner">
            <a href="index.php" class="logo">Agenda<span>Web</span></a>
            <nav class="nav">
                <a href="index.php" class="nav__link is-active">Mis eventos</a>
                <a href="registrar.php" class="nav__link">Nuevo evento</a>
                <span class="nav__link">Hola, <?= e($usuarioNombre) ?></span>
                <a href="logout.php" class="nav__link">Salir</a>
            </nav>
        </div>
    </header>

    <main class="contenedor">
        <?php if (isset($_GET['ok']) && $_GET['ok'] == '1'): ?>
            <div class="alert alert--ok" role="status">Evento guardado correctamente.</div>
        <?php endif; ?>

        <?php if (isset($_GET['editado']) && $_GET['editado'] == '1'): ?>
            <div class="alert alert--ok" role="status">Evento actualizado correctamente.</div>
        <?php endif; ?>

        <?php if (isset($_GET['borrado']) && $_GET['borrado'] == '1'): ?>
            <div class="alert alert--ok" role="status">Evento borrado correctamente.</div>
        <?php endif; ?>

        <div class="page__header">
            <div>
                <h1 class="page__title">Mis eventos</h1>
                <p class="page__subtitle">
                    <?php if ($buscando): ?>
                        <?= count($eventos) ?> resultado<?= count($eventos) === 1 ? '' : 's' ?><?= $q !== '' ? ' para «' . e($q) . '»' : '' ?>
                    <?php else: ?>
                        <?= count($eventos) ?> eventos registrados
                    <?php endif; ?>
                </p>
            </div>
            <a href="registrar.php" class="btn-primary">+ Nuevo evento</a>
        </div>

        <form class="buscador" method="get" action="index.php" role="search">
            <input class="form-input" type="search" name="q" value="<?= e($q) ?>" maxlength="60"
                   placeholder="Buscar en título o descripción…" aria-label="Buscar eventos">
            <select class="form-select" name="categoria" aria-label="Filtrar por categoría">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= $categoriaId === (int) $cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-primary">Buscar</button>
            <?php if ($buscando): ?>
                <a href="index.php" class="btn-secondary">Limpiar</a>
            <?php endif; ?>
        </form>

        <?php if (empty($eventos) && $buscando): ?>
            <div class="empty-state">
                <p>Sin resultados para tu búsqueda.</p>
                <a href="index.php" class="btn-secondary">Ver todos mis eventos</a>
            </div>
        <?php elseif (empty($eventos)): ?>
            <div class="empty-state">
                <p>Aún no tienes eventos registrados.</p>
                <a href="registrar.php" class="btn-primary">Registrar el primero</a>
            </div>
        <?php else: ?>
            <section class="card-list" aria-label="Lista de eventos">
                <?php foreach ($eventos as $ev): ?>
                    <?= mostrarEvento($ev) ?>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer class="site-footer">
        <div class="contenedor">AgendaWeb · Leslie · 2026</div>
    </footer>
</body>
</html>