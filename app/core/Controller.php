<?php

class Controller
{
    protected function view($vista, $datos = [])
    {
        // Evitar que un nombre de vista permita salir de app/views.
        if (!is_string($vista) || strpos($vista, '..') !== false) {
            http_response_code(400);
            echo 'Nombre de vista no válido.';
            return;
        }

        $ruta = ROOT_PATH . '/app/views/' . trim($vista, '/') . '.php';

        if (!is_file($ruta)) {
            http_response_code(404);
            echo 'Vista no encontrada: ' . htmlspecialchars($vista, ENT_QUOTES, 'UTF-8');
            return;
        }

        if (!is_array($datos)) {
            $datos = [];
        }

        extract($datos, EXTR_SKIP);

        require ROOT_PATH . '/app/views/layouts/header.php';
        require $ruta;
        require ROOT_PATH . '/app/views/layouts/footer.php';
    }

    protected function redirect($ruta)
    {
        $ruta = ltrim($ruta, '/');

        // Evitar repetir public/ si ya está incluido.
        if (strpos($ruta, 'public/') === 0) {
            $ruta = substr($ruta, 7);
        }

        header('Location: ' . BASE_URL . 'public/' . $ruta);
        exit();
    }
}
