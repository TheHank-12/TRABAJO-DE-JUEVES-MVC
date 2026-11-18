
<?php

// 1. Cargar configuración general
require_once dirname(__DIR__) . '/config/app.php';

// 2. Cargar conexión a la base de datos
require_once ROOT_PATH . '/config/conexion.php';

// 3. Cargar configuración de sesiones
require_once ROOT_PATH . '/config/sesion.php';

// 4. Cargar clases principales del MVC
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/core/Router.php';

// 5. Crear el enrutador
$router = new Router();

// 6. Definir ruta principal de prueba
$router->get('/', function () {
    echo "<h1>Librería Vallejo</h1>";
    echo "<p>El sistema MVC está funcionando.</p>";
});

// 7. Procesar la solicitud
$router->dispatch();

?>
