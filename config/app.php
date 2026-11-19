<?php
/**
 * Configuración general de la aplicación.
 * Se calcula la URL desde public/index.php para no depender del nombre
 * exacto de la carpeta del proyecto.
 */

date_default_timezone_set('America/Lima');

define('ROOT_PATH', dirname(__DIR__));

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$publicMarker = '/public/';
$publicPosition = strpos($scriptName, $publicMarker);

if ($publicPosition !== false) {
    $basePath = substr($scriptName, 0, $publicPosition + 1);
} else {
    // Respaldo para entornos donde SCRIPT_NAME no incluya /public/.
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/') . '/';
}

if ($basePath === '' || $basePath === './') {
    $basePath = '/';
}

define('BASE_URL', $basePath);

// Configuración MySQL local. Se pueden sobrescribir antes de cargar este archivo.
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', 'usbw');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'bd_libreria');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', 3306);
}

// Mostrar errores durante desarrollo. Desactivar en producción.
ini_set('display_errors', '1');
error_reporting(E_ALL);
