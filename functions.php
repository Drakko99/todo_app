<?php
    require_once __DIR__.'/db.php';
    require_once __DIR__.'/auth.php';

    // Registra la acción en el log
    function log_action($action, $info = '') {
        $cfg = require __DIR__.'/config.php';
        $logEntry = sprintf("[%s] %s | %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($action),
            $info
        );
        file_put_contents($cfg['log_file'], $logEntry, FILE_APPEND);
    }

    // Obtiene los ajustes
    function get_setting($key, $default = '') {
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT value FROM settings WHERE weebhook = ?');
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['value'] : $default;
    }

    // Guarda el weebhoock
    function set_setting($key, $value) {
        $pdo = get_db();
        $stmt = $pdo->prepare('INSERT INTO settings(weebhook, value) VALUES(?, ?)
                            ON DUPLICATE KEY UPDATE value = VALUES(value)');
        $stmt->execute([$key, $value]);
    }

    // Envía al webhook la accion y la tarea
    function send_webhook($action, $task = []) {
        $cfg = require __DIR__.'/config.php';
        $url = get_setting('webhook_url', $cfg['webhook_url']);
        
        if (!$url) return;
        
        $payload = [
            'action' => $action,
            'timestamp' => date('c'),
            'task' => $task
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 3
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
    
    // Obtiene una tarea
    function fetch_task($id, $user_id) {
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT td.*, te.title, te.description 
                            FROM tarea_data td 
                            INNER JOIN tarea_dataexten te ON td.id = te.id 
                            WHERE td.id = ? AND td.user_id = ?');
        $stmt->execute([$id, $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtiene la tarea para dev
    function fetch_task_any_user($id) {
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT td.*, te.title, te.description
                            FROM tarea_data td
                            INNER JOIN tarea_dataexten te ON td.id = te.id
                            WHERE td.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
