<?php

require 'conexion.php';

$eventos = [];

$resultado = $mysqli->query(
    "SELECT id, titulo, fecha, hora, categoria, descripcion
     FROM eventos
     ORDER BY fecha ASC, hora ASC"
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
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body class="layout">

<header class="site-header">
    <div class="contenedor site-header__inner">
        <a href="index.php" class="logo">
            Agenda<span>Web</span>
        </a>
        <nav class="nav">
            <a href="index.php" class="nav__link is-active">Mis eventos</a>
            <a href="registrar.php" class="nav__link">Nuevo evento</a>
        </nav>
    </div>
</header>

<main class="contenedor">

    <?php if (isset($_GET['ok']) && $_GET['ok'] == '1'): ?>
        <div class="alert alert--ok" role="status">
            &#9989; Evento guardado correctamente.
        </div>
    <?php endif; ?>

    <div class="page__header">
        <div>
            <h1 class="page__title">Mis eventos</h1>
            <p class="page__subtitle"><?= count($eventos) ?> eventos registrados</p>
        </div>

        <a href="registrar.php" class="btn-primary">+ Nuevo evento</a>
    </div>

    <?php if (!empty($eventos)): ?>

        <section class="card-list">
            <?php foreach ($eventos as $evento): ?>
                <article class="card">
                    <span class="card__badge">
                        <?= htmlspecialchars(ucfirst($evento['categoria']), ENT_QUOTES, 'UTF-8') ?>
                    </span>

                    <h2 class="card__title">
                        <?= htmlspecialchars($evento['titulo'], ENT_QUOTES, 'UTF-8') ?>
                    </h2>

                    <p class="card__meta">
                        <time>
                            <?= date('d/m/Y', strtotime($evento['fecha'])) ?>
                            <?php if (!empty($evento['hora'])): ?>
                                · <?= htmlspecialchars(substr($evento['hora'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </time>
                    </p>

                    <?php if (!empty($evento['descripcion'])): ?>
                        <p class="card__text">
                            <?= htmlspecialchars($evento['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>
                </article>
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
    <div class="contenedor">
        AgendaWeb · Leslie · 2026
    </div>
</footer>

</body>
</html>