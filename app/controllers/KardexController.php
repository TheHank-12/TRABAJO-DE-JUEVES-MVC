
<?php

class KardexController extends Controller {

    private $kardexModel;

    public function __construct() {
        requerir_personal();
        $this->kardexModel = new Kardex();
    }

    public function index() {

        $filtro_libro = isset($_GET['id_libro'])
            ? intval($_GET['id_libro'])
            : 0;

        $filtro_tipo = isset($_GET['tipo'])
            ? trim($_GET['tipo'])
            : '';

        $libros = $this->kardexModel->obtenerLibros();

        $movimientos = $this->kardexModel->listar(
            $filtro_libro,
            $filtro_tipo
        );

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
            'error'         => '',
            'csrf_token'    => generar_token_csrf()
        ]);
    }

    public function guardar() {

        // Verificar que la solicitud sea POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/kardex');
            return;
        }

        // Validar el token CSRF.
        $token = $_POST['csrf_token'] ?? '';

        if (!validar_token_csrf($token)) {

            $libros = $this->kardexModel->obtenerLibros();

            $this->view('kardex/registrar', [
                'titulo_pagina' => 'Registrar Movimiento de Kárdex',
                'libros'        => $libros,
                'error'         => 'La solicitud no pudo validarse. Recargue el formulario e inténtelo nuevamente.',
                'csrf_token'    => generar_token_csrf()
            ]);

            return;
        }

        // Obtener los datos del formulario.
        $id_libro = isset($_POST['id_libro'])
            ? intval($_POST['id_libro'])
            : 0;

        $tipo_movimiento = isset($_POST['tipo_movimiento'])
            ? trim($_POST['tipo_movimiento'])
            : '';

        $cantidad = isset($_POST['cantidad'])
            ? intval($_POST['cantidad'])
            : 0;

        $motivo = isset($_POST['motivo'])
            ? trim($_POST['motivo'])
            : '';

        // Validar los datos recibidos.
        if ($id_libro <= 0 || $tipo_movimiento === '' || $cantidad <= 0) {

            $libros = $this->kardexModel->obtenerLibros();

            $this->view('kardex/registrar', [
                'titulo_pagina' => 'Registrar Movimiento de Kárdex',
                'libros'        => $libros,
                'error'         => 'Seleccione un libro, tipo de movimiento y una cantidad válida mayor a cero.',
                'csrf_token'    => generar_token_csrf()
            ]);

            return;
        }

        // Registrar el movimiento.
        $resultado = $this->kardexModel->registrarMovimiento(
            $id_libro,
            $tipo_movimiento,
            $cantidad,
            $motivo
        );

        if ($resultado['exito']) {

            $_SESSION['mensaje'] = $resultado['mensaje'];
            $_SESSION['tipo_mensaje'] = 'success';

            $this->redirect('public/kardex');
            return;
        }

        // Mostrar el error devuelto por el modelo.
        $libros = $this->kardexModel->obtenerLibros();

        $this->view('kardex/registrar', [
            'titulo_pagina' => 'Registrar Movimiento de Kárdex',
            'libros'        => $libros,
            'error'         => $resultado['mensaje'],
            'csrf_token'    => generar_token_csrf()
        ]);
    }
}
