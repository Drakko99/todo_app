<?php
/******************************************************************************
 * API REST – tareas
 * Endpoints:
 *   GET    /api/tareas          → lista todas
 *   POST   /api/tareas          → crea una nueva
 *   GET    /api/tareas/{id}     → una tarea
 *   PUT    /api/tareas/{id}     → actualiza
 *   DELETE /api/tareas/{id}     → elimina
 *
 * PARA PROBAR:
 *
 * # LISTAR
 * curl http://localhost:8000/api.php/tareas?token=DEV
 * 
 * # DETALLE
 * curl http://localhost:8000/api.php/tareas/3?token=DEV
 *
 * # CREAR
 * curl -X POST -H "Content-Type: application/json" -d '{ "title":"Tarea de prueba", "due_date":"2025-06-30", "description":"Desde API", "user_id":1 }' http://localhost:8000/api.php/tareas?token=DEV
 *
 * # ACTUALIZAR
 * curl -X PUT -H "Content-Type: application/json" -d '{ "title":"Modificada", "status":"completada", "due_date":"2025-07-01", "description":"Actualizada", "user_id":1 }' http://localhost:8000/api.php/tareas/3?token=DEV
 *
 * # ELIMINAR
 * curl -X DELETE http://localhost:8000/api.php/tareas/3?token=DEV
 *
 ******************************************************************************/

    require_once __DIR__ . '/../db.php';
    require_once __DIR__ . '/../functions.php';
    require_once __DIR__ . '/../auth.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Asegurar que estas logeado o que le pasas un token de DEV para las pruebas
    if (!current_user_id()) {
        if (!isset($_GET['token']) || $_GET['token'] !== 'DEV') {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        $_SESSION['user_id'] = 1;
    }

    header('Content-Type: application/json; charset=utf-8');

    $pdo     = get_db();
    $uid     = current_user_id();
    $method  = $_SERVER['REQUEST_METHOD'];
    $uri     = strtok($_SERVER['REQUEST_URI'], '?');
    $matches = [];

    // Verifica que exista el usuario
    function user_exists(PDO $pdo, int $uid): bool {
        $st = $pdo->prepare('SELECT 1 FROM users WHERE id = ?');
        $st->execute([$uid]);
        return (bool) $st->fetchColumn();
    }

    // Obtener id
    if (preg_match('#/api(?:\.php)?/tareas/(\d+)$#', $uri, $matches)) {
        $id = (int)$matches[1];
    } else {
        $id = null;
    }

    function respond($data, int $code = 200): never {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    function json_input(): array {
        $raw = file_get_contents('php://input');
        $arr = json_decode($raw, true);
        return is_array($arr) ? $arr : [];
    }

    //GET /api/tareas (lista) o /{id} (detalle)
    if ($method === 'GET') {
        if ($id === null) {
            $sql = 'SELECT td.*, te.title, te.description,
                        u.username
                    FROM tarea_data td
                    JOIN tarea_dataexten te ON td.id = te.id
                    JOIN users u           ON td.user_id = u.id
                    ORDER BY td.id DESC';
            $stmt = $pdo->query($sql);
            respond($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {                           // DETALLE (id)
            $sql = 'SELECT td.*, te.title, te.description, u.username
                    FROM tarea_data td
                    JOIN tarea_dataexten te ON td.id = te.id
                    JOIN users u            ON td.user_id = u.id
                    WHERE td.id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            $task ? respond($task) : respond(['error'=>'No encontrado'],404);
        }
    }

    //POST /api/tareas (crear)
    if ($method === 'POST' && $id === null) {
        $in = json_input();

        // user_id que llega o, por defecto, el del que hace la llamada
        $targetUid = isset($in['user_id']) ? (int)$in['user_id'] : $uid;

        if (!user_exists($pdo, $targetUid)) {
            respond(['error' => "Usuario $targetUid no existe"], 404);
        }

        if (empty($in['title']) || empty($in['due_date'])) {
            respond(['error'=>'title y due_date son obligatorios'], 400);
        }

        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO tarea_data(user_id,due_date,status)
                    VALUES(?,?,?)')
            ->execute([$targetUid, $in['due_date'], $in['status'] ?? 'pendiente']);
        $newId = $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO tarea_dataexten(id,title,description)
                    VALUES(?,?,?)')
            ->execute([$newId, $in['title'], $in['description'] ?? '']);
        $pdo->commit();

        log_action('api_create', "Tarea #$newId");
        send_webhook('create', fetch_task_any_user($newId));

        respond(fetch_task_any_user($newId), 201);
    }

    //PUT /api/tareas/{id} (actualizar)
    if ($method === 'PUT' && $id !== null) {
        $task = fetch_task_any_user($id);
        $task || respond(['error'=>'No encontrado'], 404);

        $in = json_input();
        $newOwner = $in['user_id'] ?? $task['user_id'];

        if (!user_exists($pdo, $newOwner)) {
            respond(['error'=>"Usuario $newOwner no existe"], 404);
        }

        $pdo->beginTransaction();
        $pdo->prepare('UPDATE tarea_data
                    SET user_id=?, due_date=?, status=?
                    WHERE id=?')
            ->execute([
                $newOwner,
                $in['due_date'] ?? $task['due_date'],
                $in['status']   ?? $task['status'],
                $id
            ]);
        $pdo->prepare('UPDATE tarea_dataexten
                    SET title=?, description=? WHERE id=?')
            ->execute([
                $in['title']       ?? $task['title'],
                $in['description'] ?? $task['description'],
                $id
            ]);
        $pdo->commit();

        log_action('api_update', "Tarea #$id");
        send_webhook('update', fetch_task_any_user($id));

        respond(fetch_task_any_user($id));
    }

    //DELETE /api/tareas/{id}
    if ($method === 'DELETE' && $id !== null) {
        $ok = $pdo->prepare('DELETE FROM tarea_data WHERE id=?')
                ->execute([$id]);

        if ($ok) {
            log_action('api_delete', "Tarea #$id");
            send_webhook('delete', ['id'=>$id]);
            respond(['deleted' => $id]);
        } else {
            respond(['error'=>'No encontrado'],404);
        }
    }

    //En caso de metodo erroneo
    respond(['error' => 'Método o endpoint no válido'], 404);