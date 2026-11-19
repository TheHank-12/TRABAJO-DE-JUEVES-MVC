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

// Modelos y Controladores — Parte 5 (Administración y Complementos)
require_once ROOT_PATH . '/app/models/Kardex.php';
require_once ROOT_PATH . '/app/controllers/KardexController.php';
require_once ROOT_PATH . '/app/models/Proveedor.php';
require_once ROOT_PATH . '/app/controllers/ProveedorController.php';
require_once ROOT_PATH . '/app/models/Reporte.php';
require_once ROOT_PATH . '/app/controllers/ReporteController.php';
require_once ROOT_PATH . '/app/models/Resena.php';
require_once ROOT_PATH . '/app/controllers/ResenaController.php';

// 5. Crear el enrutador
$router = new Router();

// 6. Definir página principal: catálogo de libros
$router->get('/', [LibroController::class, 'catalogo']);

// Rutas de Kárdex
$router->get('/kardex', [KardexController::class, 'index']);
$router->get('/kardex/registrar', [KardexController::class, 'crear']);
$router->post('/kardex/registrar', [KardexController::class, 'guardar']);

// Rutas de Proveedores
$router->get('/proveedores', [ProveedorController::class, 'index']);
$router->get('/proveedores/crear', [ProveedorController::class, 'crear']);
$router->post('/proveedores/guardar', [ProveedorController::class, 'guardar']);
$router->get('/proveedores/editar', [ProveedorController::class, 'editar']);
$router->post('/proveedores/actualizar', [ProveedorController::class, 'actualizar']);
$router->get('/proveedores/eliminar', [ProveedorController::class, 'eliminar']);

// Rutas de Reportes y Multas
$router->get('/reportes', [ReporteController::class, 'index']);
$router->get('/reportes/imprimir', [ReporteController::class, 'imprimir']);
$router->get('/reportes/multas', [ReporteController::class, 'multas']);
$router->get('/reportes/multas/cobrar', [ReporteController::class, 'cobrarMulta']);

// Rutas de Reseñas
$router->get('/resenas', [ResenaController::class, 'index']);
$router->post('/resenas/guardar', [ResenaController::class, 'guardar']);
$router->get('/resenas/eliminar', [ResenaController::class, 'eliminar']);

// 7. Procesar la solicitud
$router->dispatch();

?>