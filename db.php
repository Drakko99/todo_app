<?php
function get_db() {
    static $pdo = null;
    
    if (!$pdo) {
        $cfg = require __DIR__.'/config.php';
        $dbConfig = $cfg['db'];
        
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
        
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    
    return $pdo;
}