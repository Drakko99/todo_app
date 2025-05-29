<?php
    require_once __DIR__.'/../auth.php';

    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit;
        }
        
        $error = 'Credenciales incorrectas';
    }

    $cfg = require __DIR__.'/../config.php';
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Login</title>
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
            <h3 class="mb-3 text-center">Iniciar Sesión</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
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
                <button class="btn btn-primary w-100">Entrar</button>
            </form>
            
            <a href="register.php" class="d-block text-center mt-2">Crear cuenta</a>
            <hr>
            <a href="settings.php" class="btn btn-outline-secondary w-100">Ajustes</a>
        </div>
    </body>
</html>