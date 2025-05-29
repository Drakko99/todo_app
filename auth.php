<?php
    // Carga la configuración
    $cfg = require __DIR__ . '/config.php';
    require_once __DIR__ . '/db.php';

    // Asegura que la sesión no está activa
    if (session_status() === PHP_SESSION_NONE) {
        session_name($cfg['session_name']);
        session_start();
    }

    // Obtiene el ID del usuario logueado
    function current_user_id() {
        return $_SESSION['user_id'] ?? null;
    }

    // Si el usuario no esta logeado lo redirige
    function ensure_logged_in(){
        if (!current_user_id()) {
            header('Location: login.php');
            exit;
        }
    }
