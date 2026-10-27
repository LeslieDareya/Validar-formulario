<?php
session_start();
if (isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
require __DIR__ . '/conexion.php';

function e(?string $texto): string {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

$nombre = $correo = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = mb_strtolower(trim($_POST['correo'] ?? ''));
    $clave  = $_POST['clave']  ?? '';
    $clave2 = $_POST['clave2'] ?? '';

    if ($nombre === '' || mb_strlen($nombre) > 80) {
        $errores['nombre'] = 'Escribe tu nombre (máximo 80 caracteres).';
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores['correo'] = 'Escribe un correo válido.';
    }
    if (mb_strlen($clave) < 8) {
        $errores['clave'] = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($clave !== $clave2) {
        $errores['clave2'] = 'Las contraseñas no coinciden.';
    }

    if (empty($errores)) {
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        try {
            $stmt = $mysqli->prepare('INSERT INTO usuarios (nombre, correo, clave_hash) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $nombre, $correo, $hash);
            $stmt->execute();
            header('Location: login.php?registrado=1');
            exit;
        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() === 1062) {
                $errores['correo'] = 'Ese correo ya tiene una cuenta.';
            } else {
                $errores['general'] = 'No se pudo crear la cuenta. Intenta de nuevo.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgendaWeb | Crear cuenta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilos.css?v=6">
</head>
<body>
    <header class="site-header">
        <div class="contenedor site-header__inner">
            <a href="login.php" class="logo">Agenda<span>Web</span></a>
        </div>
    </header>

    <main class="contenedor">
        <section class="tarjeta">
            <div class="encabezado">
                <div>
                    <h1>Crear cuenta</h1>
                    <p>Regístrate para comenzar a usar AgendaWeb.</p>
                </div>
            </div>

            <?php if (isset($errores['general'])): ?>
                <div class="alert alert--error"><?= e($errores['general']) ?></div>
            <?php endif; ?>

            <form class="formulario" method="post" action="">
                <div class="campo">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" maxlength="80" value="<?= e($nombre) ?>" required>
                    <?php if (isset($errores['nombre'])): ?>
                        <small><?= e($errores['nombre']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label for="correo">Correo</label>
                    <input type="email" id="correo" name="correo" value="<?= e($correo) ?>" required>
                    <?php if (isset($errores['correo'])): ?>
                        <small><?= e($errores['correo']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label for="clave">Contraseña</label>
                    <input type="password" id="clave" name="clave" minlength="8" autocomplete="new-password" required>
                    <?php if (isset($errores['clave'])): ?>
                        <small><?= e($errores['clave']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label for="clave2">Repítela</label>
                    <input type="password" id="clave2" name="clave2" minlength="8" autocomplete="new-password" required>
                    <?php if (isset($errores['clave2'])): ?>
                        <small><?= e($errores['clave2']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="acciones" style="justify-content: space-between; align-items: center;">
                    <a href="login.php" class="btn-secondary">Ya tengo cuenta</a>
                    <button type="submit" class="btn-primary">Crear cuenta</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="site-footer">
        <div class="contenedor">AgendaWeb · Leslie · 2026</div>
    </footer>
</body>
</html>