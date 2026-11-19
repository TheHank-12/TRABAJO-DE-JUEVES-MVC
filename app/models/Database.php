<?php

/**
 * Conexión única a MySQL mediante MySQLi.
 * Si config/app.php no se ha cargado (módulos antiguos), utiliza
 * los valores locales predeterminados definidos aquí.
 */
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'usbw');
if (!defined('DB_NAME')) define('DB_NAME', 'bd_libreria');
if (!defined('DB_PORT')) define('DB_PORT', 3306);

class Database
{
    private static $conexion = null;

    private function __construct()
    {
    }

    public static function conectar()
    {
        if (self::$conexion === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            try {
                self::$conexion = new mysqli(
                    DB_HOST,
                    DB_USER,
                    DB_PASS,
                    DB_NAME,
                    DB_PORT
                );

                self::$conexion->set_charset('utf8mb4');
            } catch (mysqli_sql_exception $e) {
                // Registrar el detalle en el servidor; no mostrar credenciales al usuario.
                error_log('Error de conexión MySQL: ' . $e->getMessage());
                throw new RuntimeException(
                    'No fue posible conectar con la base de datos. Verifique la configuración de MySQL.'
                );
            }
        }

        return self::$conexion;
    }

    public static function cerrar()
    {
        if (self::$conexion instanceof mysqli) {
            self::$conexion->close();
            self::$conexion = null;
        }
    }
}
