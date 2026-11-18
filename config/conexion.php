<?php

require_once dirname(__DIR__) . '/app/models/Database.php';

try {
    // Mantener compatibilidad con los módulos antiguos
    $conexion = Database::conectar();

} catch (Exception $e) {
    die('<h3>Error de conexión a la base de datos:</h3> ' . "<p>" .  htmlspecialchars($e->getMessage()) . "</p>");
}