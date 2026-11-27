<?php

define('DB_HOST', getenv('DB_HOST') ?: (getenv('MYSQL_ADDON_HOST') ?: 'localhost'));
define('DB_NAME', getenv('DB_NAME') ?: (getenv('MYSQL_ADDON_DB') ?: 'cexp_database'));
define('DB_USER', getenv('DB_USER') ?: (getenv('MYSQL_ADDON_USER') ?: 'root'));
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQL_ADDON_PASSWORD') !== false ? getenv('MYSQL_ADDON_PASSWORD') : ''));
define('DB_PORT', (int)(getenv('DB_PORT') ?: (getenv('MYSQL_ADDON_PORT') ?: 3306)));
?>
