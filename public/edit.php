<?php
    require_once __DIR__.'/../auth.php';
    ensure_logged_in();
    require_once __DIR__.'/../db.php';
    require_once __DIR__.'/../functions.php';

    $pdo = get_db();
    $uid = current_user_id();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $task = fetch_task($id, $uid);

    // Si no la encuentra
    if (!$task) {
        http_response_code(404);
        exit;
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'] ?? '';
        if (mb_strlen($title) > 255) {
            $title = mb_substr($title, 0, 255);
        }
        $description = $_POST['description'] ?? '';
        $dueDate = $_POST['due_date'] ?? '';
        $status = $_POST['status'] ?? 'pendiente';
        
        // Validaciones
        if (empty($title)){
           $errors[] = 'Título requerido'; 
        }
        if (empty($dueDate)) {
            $errors[] = 'Fecha requerida';
        }elseif (strtotime($dueDate) <= time()){
            $errors[] = 'La fecha debe ser futura';
        }
        
        if (empty($errors)) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('UPDATE tarea_data SET due_date=?, status=? WHERE id=? AND user_id=?');
                $stmt->execute([$dueDate, $status, $id, $uid]);
                
                $stmt = $pdo->prepare('UPDATE tarea_dataexten SET title=?, description=? WHERE id=?');
                $stmt->execute([$title, $description, $id]);
                
                $pdo->commit();
                
                $updatedTask = fetch_task($id, $uid);
                log_action('update', "Tarea #$id: \"$title\"");
                send_webhook('update', $updatedTask);
                
                header('Location: index.php');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Error al actualizar: ' . $e->getMessage();
            }
        }
    }

    $cfg = require __DIR__.'/../config.php';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Editar Tarea</title>
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
        <h1>Editar Tarea</h1>
        
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
                <input name="title" class="form-control" value="<?= htmlspecialchars($task['title']) ?>" required maxlength="255">
            </div>
            <div class="mb-3">
                <label>Descripción</label>
                <textarea name="description" class="form-control"><?= htmlspecialchars($task['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label>Fin</label>
                <input type="date" name="due_date" class="form-control" value="<?= $task['due_date'] ?>" required min="<?= date('Y-m-d') ?>">
            </div>
            <div class="mb-3">
                <label>Estado</label>
                <select name="status" class="form-select">
                    <?php foreach (['pendiente', 'en progreso', 'completada'] as $statusOption): ?>
                        <option value="<?= $statusOption ?>" <?= $task['status'] === $statusOption ? 'selected' : '' ?>>
                            <?= ucwords($statusOption) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primary">Guardar</button>
        </form>
    </body>
</html>