
<?php

class ResenaController extends Controller {

    private $resenaModel;

    public function __construct() {
        $this->resenaModel = new Resena();
    }

    // Panel administrativo de moderación
    public function index() {

        requerir_personal();

        $resenas = $this->resenaModel->listarTodas();

        $this->view('resenas/index', [
            'titulo_pagina' => 'Moderación de Reseñas',
            'resenas'       => $resenas,
            'csrf_token'    => generar_token_csrf()
        ]);
    }

    // Registro de reseñas por usuarios autenticados
    public function guardar() {

        requerir_autenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/');
            return;
        }

        // Validación CSRF
        $token = $_POST['csrf_token'] ?? '';

        if (!validar_token_csrf($token)) {
            $_SESSION['mensaje'] = 'Solicitud no válida. Actualice la página e intente nuevamente.';
            $_SESSION['tipo_mensaje'] = 'danger';

            $this->redirect('public/');
            return;
        }

        $id_libro = filter_var(
            $_POST['id_libro'] ?? null,
            FILTER_VALIDATE_INT
        );

        $puntuacion = filter_var(
            $_POST['puntuacion'] ?? null,
            FILTER_VALIDATE_INT
        );

        $comentario = trim($_POST['comentario'] ?? '');

        $id_usuario = (int) ($_SESSION['usuario_id'] ?? 0);

        // Validaciones
        if (!$id_libro || $id_libro <= 0) {

            $_SESSION['mensaje'] = 'El libro seleccionado no es válido.';
            $_SESSION['tipo_mensaje'] = 'warning';

        } elseif (!$puntuacion || $puntuacion < 1 || $puntuacion > 5) {

            $_SESSION['mensaje'] = 'La puntuación debe estar entre 1 y 5.';
            $_SESSION['tipo_mensaje'] = 'warning';

        } elseif ($comentario === '') {

            $_SESSION['mensaje'] = 'Debe ingresar un comentario para publicar su reseña.';
            $_SESSION['tipo_mensaje'] = 'warning';

        } elseif ($id_usuario <= 0) {

            $_SESSION['mensaje'] = 'No se pudo identificar al usuario.';
            $_SESSION['tipo_mensaje'] = 'danger';

        } else {

            if ($this->resenaModel->guardar(
                $id_libro,
                $id_usuario,
                $puntuacion,
                $comentario
            )) {

                $_SESSION['mensaje'] = '¡Gracias! Su reseña ha sido registrada correctamente.';
                $_SESSION['tipo_mensaje'] = 'success';

            } else {

                $_SESSION['mensaje'] = 'No se pudo publicar la reseña.';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }

        // Redirección a la página principal
        $this->redirect('public/');
    }

    // Eliminación administrativa de reseñas
    public function eliminar() {

        requerir_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/resenas');
            return;
        }

        // Validación CSRF
        $token = $_POST['csrf_token'] ?? '';

        if (!validar_token_csrf($token)) {

            $_SESSION['mensaje'] = 'Solicitud no válida. Actualice la página e intente nuevamente.';
            $_SESSION['tipo_mensaje'] = 'danger';

            $this->redirect('public/resenas');
            return;
        }

        $id_resena = filter_var(
            $_POST['id_resena'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id_resena || $id_resena <= 0) {

            $_SESSION['mensaje'] = 'Identificador de reseña no válido.';
            $_SESSION['tipo_mensaje'] = 'danger';

        } elseif ($this->resenaModel->eliminar($id_resena)) {

            $_SESSION['mensaje'] = 'Reseña eliminada correctamente.';
            $_SESSION['tipo_mensaje'] = 'success';

        } else {

            $_SESSION['mensaje'] = 'No se pudo eliminar la reseña.';
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        $this->redirect('public/resenas');
    }
}
