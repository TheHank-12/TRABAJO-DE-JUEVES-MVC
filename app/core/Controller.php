<?php

class Controller {
    protected function view($vista, $datos = []){
        extract($datos);
        $ruta = ROOT_PATH . '/app/view/' . $vista . '.php';
        if(file_exists($ruta)){
            require_once $ruta;
        } else {
            http_response_code(404);
            echo "Vista no encontrada: " . htmlspecialchars($vista);
        }
    }

    protected function redirect($ruta){
        header('Location: ' . BASE_URL . ltrim($ruta, '/'));
        exit();
    }
}