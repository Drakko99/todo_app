<?php
    require_once __DIR__.'/../auth.php';

    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Campos requeridos';
        } else {
            $pdo = get_db();
            try {
                $stmt = $pdo->prepare('INSERT INTO users(username, password_hash) VALUES(?, ?)');
                $stmt->execute([$username, password_hash($password, PASSWORD_BCRYPT)]);
                header('Location: login.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Usuario existente';
            }
        }
    }

    $cfg = require __DIR__.'/../config.php';
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Registro</title>
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
    <body class="d-flex vh-100 justify-content-center align-items-center">
        <div class="card p-4" style="width:24rem;">
            <h3 class="mb-3 text-center">Crear Cuenta</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label>Usuario</label>
                    <input name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-success w-100">Registrar</button>
            </form>
            
            <a href="login.php" class="d-block text-center mt-2">Volver a login</a>
        </div>
    </body>
</html>