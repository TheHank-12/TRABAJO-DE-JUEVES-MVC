<?php

class Router{
    private $rutas = [];

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
        $this->rutas[$metodo][$ruta] = $accion;
    }

    public function dispatch(){

        $metodo = $_SERVER['REQUEST_METHOD'];
        $ruta = parse_url($_SERVER['REQUEST_URL'], PHP_URL_PATH);
        $rutaBase = rtrim(BASE_URL, '/');
        if($rutaBase !== '' && strpos($ruta, $rutaBase) === 0){
            $ruta = substr($ruta, strlen($rutaBase));
        }

        $ruta = '/' . ltrim($ruta, '/');

        if($ruta === '//') {
            $ruta = '/';
        }

    if (isset($this->rutas[$metodo][$ruta])) {
        $accion = $this->rutas[$metodo][$ruta];
        if (is_callable($accion)) {
                call_user_func($accion);
                return;
            }

            if (is_array($accion)) {
                [$controlador, $metodoControlador] = $accion;

                $objeto = new $controlador();
                $objeto->$metodoControlador();
                return;
            }
        }

        http_response_code(404);
        echo "Página no encontrada.";
    }

}