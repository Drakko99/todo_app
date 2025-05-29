<?php
    require_once __DIR__.'/../auth.php';
    require_once __DIR__.'/../functions.php';
    ensure_logged_in();

    $pdo = get_db();
    $uid = current_user_id();

    // Ordenar
    $columns = [
        'created_at' => 'Creación',
        'due_date' => 'Fin',
        'title' => 'Tarea',
        'status' => 'Estado'
    ];

    $sort = $_GET['sort'] ?? 'created_at';
    if (!array_key_exists($sort, $columns)) $sort = 'created_at';

    $dir = strtoupper($_GET['dir'] ?? 'DESC');
    if (!in_array($dir, ['ASC', 'DESC'])) $dir = 'DESC';

    $orderSql = "$sort $dir";
    $searchTerm = trim($_GET['q'] ?? '');

    $sql = 'SELECT td.id, td.created_at, td.due_date, td.status, te.title, te.description
            FROM tarea_data td
            INNER JOIN tarea_dataexten te ON td.id = te.id
            WHERE td.user_id = ?';
    $params = [$uid];

    if ($searchTerm !== '') {
        $likeTerm = '%' . $searchTerm . '%';
        $sql .= ' AND (te.title LIKE ? OR te.description LIKE ?)';
        $params[] = $likeTerm;
        $params[] = $likeTerm;
    }

    $sql .= " ORDER BY $orderSql";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    function badge($status) {
        $class = match ($status) {
            'pendiente' => 'badge-pendiente',
            'en progreso' => 'badge-en-progreso',
            default => 'badge-completada',
        };
        return '<span class="badge ' . $class . '">' . ucwords($status) . '</span>';
    }

    $cfg = require __DIR__.'/../config.php';
?>

<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Mis Tareas</title>
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
        <nav class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="m-0">Mis Tareas</h2>
            <div>
                <a href="settings.php" class="btn btn-outline-secondary me-2">Ajustes</a>
                <a href="logout.php" class="btn btn-outline-danger">Salir</a>
            </div>
        </nav>

        <div class="row g-3 mb-3">
            <div class="col-sm">
                <label class="form-label mb-0">Buscar</label>
                <input id="search-box" type="text" class="form-control" placeholder="Escribe..." value="<?= htmlspecialchars($searchTerm) ?>">
            </div>
            <div class="col-auto d-flex flex-column">
                <label class="form-label mb-0">Ordenar por</label>
                <select id="sort" class="form-select">
                    <?php foreach ($columns as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $key === $sort ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex flex-column">
                <label class="form-label mb-0 invisible">Dirección</label>
                <button id="dirBtn" class="btn btn-outline-secondary"><?= $dir === 'ASC' ? '↑' : '↓' ?></button>
            </div>
        </div>

        <div class="d-flex gap-2 mb-3">
            <a href="create.php" class="btn btn-primary">Nueva Tarea</a>
            <a href="log_view.php" class="btn btn-secondary">Ver Log</a>
        </div>

        <table id="tasks-table" class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Tarea</th>
                    <th>Estado</th>
                    <th>Creación</th>
                    <th>Fin</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($task['title']) ?></div>
                            <div class="desc"><?= htmlspecialchars($task['description']) ?></div>
                        </td>
                        <td><?= badge($task['status']) ?></td>
                        <td><?= $task['created_at'] ?></td>
                        <td><?= $task['due_date'] ?></td>
                        <td>
                            <a href="edit.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="delete.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($tasks) === 0): ?>
                    <tr id="no-data">
                        <td colspan="5" class="text-center text-muted">Sin tareas</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <script>
            const search = document.getElementById('search-box');
            const sortSelect = document.getElementById('sort');
            const dirButton = document.getElementById('dirBtn');
            
            search.addEventListener('input', () => {
                const term = search.value.toLowerCase();
                let visibleCount = 0;
                
                document.querySelectorAll('#tasks-table tbody tr').forEach(row => {
                    if (row.id !== 'no-data') {
                        const title = row.querySelector('.fw-bold').textContent.toLowerCase();
                        const description = row.querySelector('.desc').textContent.toLowerCase();
                        const matches = title.includes(term) || description.includes(term);
                        
                        row.style.display = matches ? '' : 'none';
                        if (matches) visibleCount++;
                    }
                });
                
                const noDataRow = document.getElementById('no-data');
                if (noDataRow) {
                    noDataRow.style.display = visibleCount ? 'none' : '';
                }
            });
            
            function updateParams() {
                const params = new URLSearchParams();
                params.set('q', search.value);
                params.set('sort', sortSelect.value);
                params.set('dir', dirButton.textContent === '↑' ? 'ASC' : 'DESC');
                window.location.search = params.toString();
            }
            
            sortSelect.addEventListener('change', updateParams);
            dirButton.addEventListener('click', e => {
                e.preventDefault();
                dirButton.textContent = dirButton.textContent === '↑' ? '↓' : '↑';
                updateParams();
            });
        </script>
    </body>
</html>