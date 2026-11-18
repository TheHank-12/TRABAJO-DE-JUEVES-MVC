
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

// Cargar modelos y controladores
require_once ROOT_PATH . '/app/models/Libro.php';
require_once ROOT_PATH . '/app/controllers/LibroController.php';

// 5. Crear el enrutador
$router = new Router();

// 6. Definir pagina principal: catalogo de libros
$router->get('/', [LibroController::class, 'catalogo']);

// 7. Procesar la solicitud
$router->dispatch();

?>
