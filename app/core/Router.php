
<?php

class Router
{
    private $rutas = [];

    // Registrar rutas GET
    public function get($ruta, $accion)
    {
        $this->registrar('GET', $ruta, $accion);
    }

    // Registrar rutas POST
    public function post($ruta, $accion)
    {
        $this->registrar('POST', $ruta, $accion);
    }

    // Guardar las rutas
    private function registrar($metodo, $ruta, $accion)
    {
        $this->rutas[$metodo][$ruta] = $accion;
    }

    // Ejecutar la ruta solicitada
    public function dispatch()
    {
        $metodo = $_SERVER['REQUEST_METHOD'];

        
        // Obtener la dirección solicitada
        $ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Eliminar la dirección base del proyecto
        $rutaBase = rtrim(BASE_URL, '/');

        if ($rutaBase !== '' && strpos($ruta, $rutaBase) === 0) {
            $ruta = substr($ruta, strlen($rutaBase));
        }

        // Eliminar /public de la dirección
        if ($ruta === '/public' || strpos($ruta, '/public/') === 0) {
            $ruta = substr($ruta, 7);
        }

        // Normalizar la ruta
        $ruta = '/' . trim($ruta, '/');


        // Comprobar si existe la ruta
        if (isset($this->rutas[$metodo][$ruta])) {

            $accion = $this->rutas[$metodo][$ruta];

            // Ejecutar función
            if (is_callable($accion)) {
                call_user_func($accion);
                return;
            }

            // Ejecutar método de un controlador
            if (is_array($accion)) {
                [$controlador, $metodoControlador] = $accion;

                $objeto = new $controlador();
                $objeto->$metodoControlador();
                return;
            }
        }

        // Si la ruta no existe
        http_response_code(404);
        echo "Página no encontrada.";
    }
}
