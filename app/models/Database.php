<?php
class Database {
    private static $conexion = null;
    public static function conectar() {
        if (self::$conexion === null) {
            $host = 'localhost';
            $usuario = 'root';
            $password = 'usbw';
            $base_de_datos = 'bd_libreria';
            $puerto = 3306;

            self::$conexion = new mysqli($host, $usuario, $password, $base_de_datos, $puerto);

            if (self::$conexion->connect_error) {
                throw new Exception('Error de conexión a la base de datos: ' . self::$conexion->connect_error);
            }
            self::$conexion->set_charset('utf8mb4');
        }
        return self::$conexion;
    }
}