<?php
    require_once __DIR__.'/../auth.php';
    require_once __DIR__.'/../functions.php';
    ensure_logged_in();
    $cfg = require __DIR__.'/../config.php';

    $logContent = '';
    $logFilePath = $cfg['log_file'];

    // Si esta vacio o no existe muetra un mensaje
    if (!file_exists($logFilePath)) {
        $logContent = 'El log no existe.';
    } else {
        $logContent = trim(file_get_contents($logFilePath));
        if ($logContent === '') {
            $logContent = 'El log está vacío.';
        }
    }

?>

<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Registro</title>
        <link id="themeCss" rel="stylesheet" href="<?= $cfg['theme_css_light'] ?>">
        <link rel="stylesheet" href="custom.css">
        <script>
            (function() {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark') {
                    document.getElementById('themeCss').href = '<?= $cfg['theme_css_dark'] ?>';
                }
            })();
        </script>
    </head>
    <body class="container py-4">
        <nav>
            <a href="index.php" class="btn btn-secondary mb-3">← Volver</a>
        </nav>
        <h1>Registro de Acciones</h1>
        <pre class="pre-log p-3 border rounded"><?= htmlspecialchars($logContent) ?></pre>
    </body>
</html>