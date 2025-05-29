<?php
    $cfg = require __DIR__ . '/../config.php';
    $themeLight = $cfg['theme_css_light'];
    $themeDark = $cfg['theme_css_dark'];
?>

<script>
function applyTheme() {
    const themeCss = document.getElementById('themeCss');
    const themeSwitch = document.getElementById('themeSwitch');
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    if (themeCss) {
        themeCss.href = savedTheme === 'dark' ? '<?= $themeDark ?>' : '<?= $themeLight ?>';
    }
    
    if (themeSwitch) {
        themeSwitch.checked = savedTheme === 'dark';
        themeSwitch.addEventListener('change', () => {
            const newTheme = themeSwitch.checked ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);
            themeCss.href = newTheme === 'dark' ? '<?= $themeDark ?>' : '<?= $themeLight ?>';
        });
    }
}
</script>