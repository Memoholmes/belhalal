<?php
if(isset($_GET['lang']) and $_GET['lang']) {
    changeAppLocale($_GET['lang']);
} else {
    changeAppLocale();
}

// re-init configs for translations
config([
    '__tech' => require config_path('__tech.php'),
    '__settings' => require config_path('__settings.php'),
]);