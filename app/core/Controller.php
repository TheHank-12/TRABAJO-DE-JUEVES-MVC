<?php

class Controller {
    protected function view($vista, $datos = []){
        extract($datos);
        $ruta = ROOT_PATH . '/app/views/' . $vista . '.php';
        if(file_exists($ruta)){

            //encabezado
            require_once ROOT_PATH . '/app/views/layouts/header.php';
            //vista
            require_once $ruta;
            //pie de página
            require_once ROOT_PATH . '/app/views/layouts/footer.php';
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