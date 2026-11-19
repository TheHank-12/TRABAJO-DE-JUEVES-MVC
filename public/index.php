
<?php

// Punto de entrada único de la aplicación MVC.

require_once dirname(__DIR__) . '/config/app.php';
require_once ROOT_PATH . '/config/sesion.php';

require_once ROOT_PATH . '/app/models/Database.php';
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/core/Router.php';

// Modelos y controladores del cuarto integrante.
require_once ROOT_PATH . '/app/models/Carrito.php';
require_once ROOT_PATH . '/app/controllers/CarritoController.php';

require_once ROOT_PATH . '/app/models/Venta.php';
require_once ROOT_PATH . '/app/controllers/VentaController.php';

require_once ROOT_PATH . '/app/models/PuntoVenta.php';
require_once ROOT_PATH . '/app/controllers/PuntoVentaController.php';

// Modelos y controladores del quinto integrante.
require_once ROOT_PATH . '/app/models/Kardex.php';
require_once ROOT_PATH . '/app/controllers/KardexController.php';

require_once ROOT_PATH . '/app/models/Proveedor.php';
require_once ROOT_PATH . '/app/controllers/ProveedorController.php';

require_once ROOT_PATH . '/app/models/Reporte.php';
require_once ROOT_PATH . '/app/controllers/ReporteController.php';

require_once ROOT_PATH . '/app/models/Resena.php';
require_once ROOT_PATH . '/app/controllers/ResenaController.php';

// Autocarga nativa de modelos y controladores (sin framework).
spl_autoload_register(function ($clase) {
    $directorios = [
        ROOT_PATH . '/app/models/',
        ROOT_PATH . '/app/controllers/'
    ];

    foreach ($directorios as $directorio) {
        $archivo = $directorio . $clase . '.php';

        if (is_file($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

// Crear el enrutador.
$router = new Router();

// Catálogo público.
$router->get('/', [LibroController::class, 'catalogo']);
$router->get('/catalogo', [LibroController::class, 'catalogo']);
// Autenticación.
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/registro', [AuthController::class, 'registro']);
$router->post('/registro', [AuthController::class, 'registro']);

$router->post('/logout', [AuthController::class, 'logout']);

// Gestión de libros.
$router->get('/libros', [LibroController::class, 'gestion']);
$router->get('/libros/crear', [LibroController::class, 'formulario']);
$router->get('/libros/editar', [LibroController::class, 'formulario']);
$router->get('/libros/detalle', [LibroController::class, 'detalle']);

$router->post('/libros/guardar', [LibroController::class, 'guardar']);
$router->post('/libros/eliminar', [LibroController::class, 'eliminar']);
$router->post('/libros/resena', [LibroController::class, 'resena']);

// Gestión de categorías, autores y editoriales.
foreach (['categorias', 'autores', 'editoriales'] as $seccionCatalogo) {

    $router->get(
        '/' . $seccionCatalogo,
        [CatalogoController::class, 'gestion']
    );

    $router->get(
        '/' . $seccionCatalogo . '/crear',
        [CatalogoController::class, 'formulario']
    );

    $router->get(
        '/' . $seccionCatalogo . '/editar',
        [CatalogoController::class, 'formulario']
    );

    $router->post(
        '/' . $seccionCatalogo . '/guardar',
        [CatalogoController::class, 'guardar']
    );

    $router->post(
        '/' . $seccionCatalogo . '/eliminar',
        [CatalogoController::class, 'eliminar']
    );
}

// RUTAS DEL QUINTO INTEGRANTE

// Kárdex.
$router->get('/kardex', [KardexController::class, 'index']);
$router->get('/kardex/registrar', [KardexController::class, 'crear']);
$router->post('/kardex/registrar', [KardexController::class, 'guardar']);

// Rutas de proveedores.
$router->get('/proveedores', [ProveedorController::class, 'index']);
$router->get('/proveedores/crear', [ProveedorController::class, 'crear']);
$router->post('/proveedores/guardar', [ProveedorController::class, 'guardar']);
$router->get('/proveedores/editar', [ProveedorController::class, 'editar']);
$router->post('/proveedores/actualizar', [ProveedorController::class, 'actualizar']);
$router->post('/proveedores/eliminar', [ProveedorController::class, 'eliminar']);

// Reportes.
$router->get('/reportes', [ReporteController::class, 'index']);
$router->get('/reportes/imprimir', [ReporteController::class, 'imprimir']);
$router->get('/reportes/multas', [ReporteController::class, 'multas']);
$router->post('/reportes/cobrar-multa', [ReporteController::class, 'cobrarMulta']);

// Reseñas
$router->get('/resenas', [ResenaController::class, 'index']);
$router->post('/resenas/guardar', [ResenaController::class, 'guardar']);
$router->post('/resenas/eliminar', [ResenaController::class, 'eliminar']);


// Carrito de compras.
$router->get('/carrito', [CarritoController::class, 'index']);
$router->post('/carrito/agregar', [CarritoController::class, 'agregar']);
$router->post('/carrito/actualizar', [CarritoController::class, 'actualizar']);
$router->post('/carrito/eliminar', [CarritoController::class, 'eliminar']);
$router->post('/carrito/vaciar', [CarritoController::class, 'vaciar']);
$router->post('/carrito/procesar', [CarritoController::class, 'procesar']);

// Módulo de ventas
$router->get('/ventas', [VentaController::class, 'index']);
$router->get('/ventas/comprobante', [VentaController::class, 'comprobante']);
$router->get('/mis-compras', [VentaController::class, 'misCompras']);

// Punto de Venta (POS)
$router->get('/ventas/crear', [PuntoVentaController::class, 'index']);
$router->post('/ventas/guardar', [PuntoVentaController::class, 'guardar']);

// Módulo de alquileres.
$router->get('/alquileres', [AlquilerController::class, 'index']);
$router->get('/alquileres/crear', [AlquilerController::class, 'crear']);
$router->post('/alquileres/guardar', [AlquilerController::class, 'guardar']);

$router->get('/alquileres/detalle', [AlquilerController::class, 'detalle']);
$router->post('/alquileres/devolver', [AlquilerController::class, 'devolver']);

// Gestión de multas.
$router->get('/alquileres/multas', [AlquilerController::class, 'multas']);
$router->post('/alquileres/cobrar-multa', [AlquilerController::class, 'cobrarMulta']);

// Alquileres del cliente.
$router->get('/mis-alquileres', [AlquilerController::class, 'misAlquileres']);
$router->get('/mis-multas', [AlquilerController::class, 'misMultas']);
// Solicitud de alquiler desde el catálogo (cliente).
$router->get('/solicitar-alquiler', [AlquilerController::class, 'solicitar']);
$router->post('/solicitar-alquiler/guardar', [AlquilerController::class, 'guardarSolicitud']);


// Módulo de usuarios (solo administrador).
$router->get('/usuarios', [UsuarioController::class, 'index']);

$router->get('/usuarios/crear', [UsuarioController::class, 'crear']);
$router->post('/usuarios/guardar', [UsuarioController::class, 'guardar']);

$router->get('/usuarios/editar', [UsuarioController::class, 'editar']);
$router->post('/usuarios/actualizar', [UsuarioController::class, 'actualizar']);

$router->post('/usuarios/estado', [UsuarioController::class, 'cambiarEstado']);
$router->post('/usuarios/eliminar', [UsuarioController::class, 'eliminar']);


// Procesar la solicitud.
$router->dispatch();
