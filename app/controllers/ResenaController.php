<?php

class ResenaController extends Controller {
    private $resenaModel;

    public function __construct() {
        $this->resenaModel = new Resena();
    }

    // Panel administrativo de moderación de reseñas
    public function index() {
        requerir_personal();
        $resenas = $this->resenaModel->listarTodas();

        $this->view('resenas/index', [
            'titulo_pagina' => 'Moderación de Reseñas',
            'resenas'       => $resenas
        ]);
    }

    // Registro de reseña enviado por el cliente desde el detalle del libro
    public function guardar() {
        requerir_autenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/');
        }

        $id_libro   = isset($_POST['id_libro']) ? intval($_POST['id_libro']) : 0;
        $puntuacion = isset($_POST['puntuacion']) ? intval($_POST['puntuacion']) : 5;
        $comentario = trim($_POST['comentario'] ?? '');
        $id_usuario = (int)$_SESSION['usuario_id'];

        if ($puntuacion < 1) $puntuacion = 1;
        if ($puntuacion > 5) $puntuacion = 5;

        if ($id_libro > 0 && !empty($comentario)) {
            if ($this->resenaModel->guardar($id_libro, $id_usuario, $puntuacion, $comentario)) {
                $_SESSION['mensaje'] = '¡Gracias! Su reseña ha sido registrada correctamente.';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al publicar la reseña.';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        } else {
            $_SESSION['mensaje'] = 'Por favor ingrese un comentario válido para enviar su calificación.';
            $_SESSION['tipo_mensaje'] = 'warning';
        }

        if (!empty($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $this->redirect('public/');
    }

    // Eliminación administrativa de reseñas inapropiadas
    public function eliminar() {
        requerir_admin();
        $id_resena = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id_resena > 0 && $this->resenaModel->eliminar($id_resena)) {
            $_SESSION['mensaje'] = 'Reseña eliminada correctamente.';
            $_SESSION['tipo_mensaje'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'No se pudo eliminar la reseña.';
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        $this->redirect('public/resenas');
    }
}