<?php
//Zona horaria
date_default_timezone_set('America/Lima');
//Ruta principal del proyecto
define('root_path',dirname(__DIR__));

//URL
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($scriptDir, '/') . '/');

//Mostrar errores en desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

?>