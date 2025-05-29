<?php
    return [
        'db' => [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'name' => 'todo_app',
            'charset' => 'utf8mb4'
        ],
        'webhook_url' => '',
        'theme_css_light' => 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/quartz/bootstrap.min.css',
        'theme_css_dark' => 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/vapor/bootstrap.min.css',
        'log_file' => __DIR__ . '/storage/actions.log',
        'session_name' => 'todoapp_sid'
    ];