<?php
    require_once __DIR__.'/../auth.php';
    ensure_logged_in();
    require_once __DIR__.'/../db.php';
    require_once __DIR__.'/../functions.php';

    $pdo = get_db();
    $uid = current_user_id();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $task = fetch_task($id, $uid);

    // Si no encuentra la tarea
    if (!$task) {
        http_response_code(404);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare('DELETE FROM tarea_data WHERE id=? AND user_id=?');
        $stmt->execute([$id, $uid]);
        
        log_action('delete', "Tarea #$id");
        send_webhook('delete', ['id' => $id]);
        
        header('Location: index.php');
        exit;
    }

    $cfg = require __DIR__.'/../config.php';
?>

<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Eliminar Tarea</title>
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
        <h1>Eliminar Tarea</h1>
        <p>¿Seguro que deseas eliminar <strong><?= htmlspecialchars($task['title']) ?></strong>?</p>
        <form method="post">
            <button class="btn btn-danger">Eliminar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </body>
</html>