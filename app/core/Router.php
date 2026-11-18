
<?php

class Router
{
    private $rutas = [];
    /**
     * Registra una nueva ruta con su método y acción correspondiente.
     */
    public function get($ruta, $accion)
    {
        $this->registrar('GET', $ruta, $accion);
    }
    /**
     * Registra una nueva ruta con su método y acción correspondiente.
     */
    public function post($ruta, $accion)
    {
        $this->registrar('POST', $ruta, $accion);
    }
    /**
     * Registra una nueva ruta con su método y acción correspondiente.
     */
    private function registrar($metodo, $ruta, $accion)
    {
        $this->rutas[$metodo][$ruta] = $accion;
    }
    /**
     * Despacha la solicitud entrante a la acción correspondiente.
     */
    public function dispatch()
    {
        $metodo = $_SERVER['REQUEST_METHOD'];

        // Obtener la ruta solicitada
        $ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Eliminar la carpeta base del proyecto
        $rutaBase = rtrim(BASE_URL, '/');

        if ($rutaBase !== '' && strpos($ruta, $rutaBase) === 0) {
            $ruta = substr($ruta, strlen($rutaBase));
        }

        // Eliminar /public si está presente
        $ruta = preg_replace('#^/public(?:/|$)#', '', $ruta);

        // Normalizar la ruta
        $ruta = '/' . trim($ruta, '/');
        // Si la ruta es solo '/', mantenerla como '/'
        // Comprobar si existe la ruta
        if (isset($this->rutas[$metodo][$ruta])) {

            $accion = $this->rutas[$metodo][$ruta];
            // Si la acción es una función anónima, ejecutarla directamente
            if (is_callable($accion)) {
                call_user_func($accion);
                return;
            }
            // Si la acción es un arreglo [Controlador, Método], instanciar el controlador y llamar al método
            if (is_array($accion)) {
                [$controlador, $metodoControlador] = $accion;

                $objeto = new $controlador();
                $objeto->$metodoControlador();
                return;
            }
        }
        // Si no se encuentra la ruta, mostrar un error 404
        http_response_code(404);
        echo "Página no encontrada. Ruta recibida: " . htmlspecialchars($ruta);
    }
}
