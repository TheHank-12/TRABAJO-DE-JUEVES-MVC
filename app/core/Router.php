<?php

class Router
{
    private $rutas = [
        'GET' => [],
        'POST' => []
    ];

    public function get($ruta, $accion)
    {
        $this->registrar('GET', $ruta, $accion);
    }

    public function post($ruta, $accion)
    {
        $this->registrar('POST', $ruta, $accion);
    }

    private function registrar($metodo, $ruta, $accion)
    {
        $ruta = $this->normalizarRuta($ruta);
        $this->rutas[$metodo][$ruta] = $accion;
    }

    private function normalizarRuta($ruta)
    {
        $ruta = '/' . trim((string) $ruta, '/');
        return $ruta === '//' ? '/' : $ruta;
    }

    private function obtenerRutaSolicitada()
    {
        $ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $ruta = is_string($ruta) ? $ruta : '/';

        // Retirar la carpeta base del proyecto.
        $rutaBase = rtrim(BASE_URL, '/');
        if ($rutaBase !== '' && strpos($ruta, $rutaBase . '/') === 0) {
            $ruta = substr($ruta, strlen($rutaBase));
        } elseif ($ruta === $rutaBase) {
            $ruta = '/';
        }

        // Permite acceder tanto desde /public/ como desde su raíz de entrada.
        $ruta = preg_replace('#^/?public(?:/|$)#', '/', $ruta);

        return $this->normalizarRuta($ruta);
    }

    
    public function dispatch()
    {
        $metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $ruta = $this->obtenerRutaSolicitada();

        if (!isset($this->rutas[$metodo])) {
            http_response_code(405);
            header('Allow: GET, POST');
            echo 'Método HTTP no permitido.';
            return;
        }

        if (!isset($this->rutas[$metodo][$ruta])) {
            http_response_code(404);
            echo 'Página no encontrada. Ruta: ' .
                htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8');
            return;
        }

        $accion = $this->rutas[$metodo][$ruta];

        // Acciones mediante funciones o callbacks.
        if (is_callable($accion)) {
            call_user_func($accion);
            return;
        }

        // Acciones mediante controlador y método.
        if (
            is_array($accion) &&
            count($accion) === 2 &&
            is_string($accion[0]) &&
            is_string($accion[1])
        ) {
            $nombreControlador = $accion[0];
            $nombreMetodo = $accion[1];

            // Intentar cargar el controlador mediante autoload.
            if (!class_exists($nombreControlador)) {
                http_response_code(500);
                echo 'No se pudo cargar el controlador: ' .
                    htmlspecialchars($nombreControlador, ENT_QUOTES, 'UTF-8');
                return;
            }

            $controlador = new $nombreControlador();

            // Comprobar que el método exista y pueda ejecutarse.
            if (!is_callable([$controlador, $nombreMetodo])) {
                http_response_code(500);
                echo 'El método ' .
                    htmlspecialchars($nombreMetodo, ENT_QUOTES, 'UTF-8') .
                    ' no existe o no es accesible en ' .
                    htmlspecialchars($nombreControlador, ENT_QUOTES, 'UTF-8');
                return;
            }

            $controlador->$nombreMetodo();
            return;
        }

        http_response_code(500);
        echo 'La acción configurada para esta ruta no es válida.';
    }

}
