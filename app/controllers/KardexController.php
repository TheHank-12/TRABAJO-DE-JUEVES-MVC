<?php

class KardexController extends Controller {
    private $kardexModel;

    public function __construct() {
        requerir_personal();
        $this->kardexModel = new Kardex();
    }

    public function index() {
        $filtro_libro = isset($_GET['id_libro']) ? intval($_GET['id_libro']) : 0;
        $filtro_tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';

        $libros = $this->kardexModel->obtenerLibros();
        $movimientos = $this->kardexModel->listar($filtro_libro, $filtro_tipo);

        $this->view('kardex/index', [
            'titulo_pagina' => 'Kárdex de Inventario',
            'libros'        => $libros,
            'movimientos'   => $movimientos,
            'filtro_libro'  => $filtro_libro,
            'filtro_tipo'   => $filtro_tipo
        ]);
    }

    public function crear() {
        $libros = $this->kardexModel->obtenerLibros();

        $this->view('kardex/registrar', [
            'titulo_pagina' => 'Registrar Movimiento de Kárdex',
            'libros'        => $libros,
            'error'         => ''
        ]);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/kardex');
        }

        $id_libro        = isset($_POST['id_libro']) ? intval($_POST['id_libro']) : 0;
        $tipo_movimiento = isset($_POST['tipo_movimiento']) ? trim($_POST['tipo_movimiento']) : '';
        $cantidad        = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
        $motivo          = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';

        if ($id_libro === 0 || empty($tipo_movimiento) || $cantidad <= 0) {
            $libros = $this->kardexModel->obtenerLibros();
            $this->view('kardex/registrar', [
                'titulo_pagina' => 'Registrar Movimiento de Kárdex',
                'libros'        => $libros,
                'error'         => 'Seleccione un libro, tipo de movimiento y una cantidad válida mayor a cero.'
            ]);
            return;
        }

        $resultado = $this->kardexModel->registrarMovimiento($id_libro, $tipo_movimiento, $cantidad, $motivo);

        if ($resultado['exito']) {
            $_SESSION['mensaje'] = $resultado['mensaje'];
            $_SESSION['tipo_mensaje'] = 'success';
            $this->redirect('public/kardex');
        } else {
            $libros = $this->kardexModel->obtenerLibros();
            $this->view('kardex/registrar', [
                'titulo_pagina' => 'Registrar Movimiento de Kárdex',
                'libros'        => $libros,
                'error'         => $resultado['mensaje']
            ]);
        }
    }
}