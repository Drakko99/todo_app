<?php
    require_once __DIR__.'/../auth.php';
    ensure_logged_in();
    require_once __DIR__.'/../db.php';
    require_once __DIR__.'/../functions.php';

    $pdo = get_db();
    $uid = current_user_id();
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $dueDate = $_POST['due_date'] ?? '';
        $status = $_POST['status'] ?? 'pendiente';
        
        // Validaciones
        if (empty($title)) {
            $errors[] = 'Título requerido';
        }
        if (empty($dueDate)) {
            $errors[] = 'Fecha requerida';
        } else {
            $today = strtotime(date('Y-m-d'));
            $selectedDate = strtotime($dueDate);
            
            if ($selectedDate < $today) {
                $errors[] = 'La fecha no puede ser anterior a hoy';
            }
        }
        
        // Si no hay errores la crea
        if (empty($errors)) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('INSERT INTO tarea_data(user_id, due_date, status) VALUES(?, ?, ?)');
                $stmt->execute([$uid, $dueDate, $status]);
                $taskId = $pdo->lastInsertId();
                
                $stmt = $pdo->prepare('INSERT INTO tarea_dataexten(id, title, description) VALUES(?, ?, ?)');
                $stmt->execute([$taskId, $title, $description]);
                
                $pdo->commit();
                
                $task = fetch_task($taskId, $uid);
                log_action('create', "Tarea #$taskId");
                send_webhook('create', $task);
                
                header('Location: index.php');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Error al crear la tarea: ' . $e->getMessage();
            }
        }
    }

    $cfg = require __DIR__.'/../config.php';
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Nueva Tarea</title>
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
        <h1>Nueva Tarea</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" class="card p-4">
            <div class="mb-3">
                <label>Título</label>
                <input name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Descripción</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Fin</label>
                <input type="date" name="due_date" class="form-control" required min="<?= date('Y-m-d') ?>">
            </div>
            <div class="mb-3">
                <label>Estado</label>
                <select name="status" class="form-select">
                    <option value="pendiente">Pendiente</option>
                    <option value="en progreso">En progreso</option>
                    <option value="completada">Completada</option>
                </select>
            </div>
            <button class="btn btn-success">Crear</button>
        </form>
    </body>
</html>