<?php
  $cfg = require __DIR__ . '/../config.php';
  session_name($cfg['session_name']);

  // Iniciar sesión solo si no está activa
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }

  require_once __DIR__ . '/../functions.php';
  $ok = '';
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      set_setting('webhook_url', trim($_POST['webhook_url'] ?? ''));
      $ok = 'URL actualizada';
  }

  $cur = get_setting('webhook_url', '');
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Ajustes</title>
    <link id="themeCss" rel="stylesheet" href="<?= $cfg['theme_css_light'] ?>">
    <script>
      (function() {
        const theme = localStorage.getItem('theme');
        if (theme === 'dark') {
          document.getElementById('themeCss').href = '<?= $cfg['theme_css_dark'] ?>';
        }
      })();
    </script>
    <link rel="stylesheet" href="custom.css">
  </head>

  <body class="container py-4">

    <nav class="d-flex justify-content-between align-items-center mb-3">
      <a href="#" onclick="history.back();return false;" class="btn btn-secondary">← Volver</a>
      <h2 class="m-0">Ajustes</h2>
      <div class="d-flex align-items-center ms-auto">
        ☀️
        <div class="form-check form-switch mx-2">
          <input class="form-check-input" type="checkbox" id="themeSwitch">
        </div>
        🌙
      </div>
    </nav>

    <?php if ($ok): ?>
      <div class="alert alert-success"><?= $ok ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 mx-auto" style="max-width:600px;">
      <div class="mb-3">
        <label>Webhook URL</label>
        <input name="webhook_url" value="<?= htmlspecialchars($cur) ?>" class="form-control">
      </div>
      <button class="btn btn-primary">Guardar</button>
    </form>

    <script>
      (function() {
        const css = document.getElementById('themeCss');
        const sw = document.getElementById('themeSwitch');
        const LIGHT = '<?= $cfg['theme_css_light'] ?>';
        const DARK = '<?= $cfg['theme_css_dark'] ?>';

        // Inicializar el switch
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (sw) {
          sw.checked = currentTheme === 'dark';
        }

        // Aplicar tema inicial
        if (currentTheme === 'dark') {
          css.href = DARK;
        }
        
        if (sw) {
          sw.addEventListener('change', () => {
            if (sw.checked) {
              css.href = DARK;
              localStorage.setItem('theme', 'dark');
            } else {
              css.href = LIGHT;
              localStorage.setItem('theme', 'light');
            }
          });
        }
      })();
    </script>
  </body>
</html>