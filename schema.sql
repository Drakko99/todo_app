DROP DATABASE IF EXISTS todo_app;

CREATE DATABASE IF NOT EXISTS todo_app CHARACTER SET utf8mb4;

USE todo_app;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    weebhook VARCHAR(50) PRIMARY KEY,
    value TEXT
);

CREATE TABLE IF NOT EXISTS tarea_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    due_date DATE,
    status ENUM('pendiente','en progreso','completada') DEFAULT 'pendiente',
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tarea_dataexten (
    id INT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    FOREIGN KEY(id) REFERENCES tarea_data(id) ON DELETE CASCADE
);