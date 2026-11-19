
<?php

class AlquilerController extends Controller
{
    private $alquilerModel;

    public function __construct()
    {
        $this->alquilerModel = new Alquiler();
    }

    // Generar token CSRF
    private function token()
    {
        if (empty($_SESSION['csrf_alquiler'])) {
            $_SESSION['csrf_alquiler'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_alquiler'];
    }

    // Validar token CSRF
    private function validarToken()
    {
        $actual = (string) ($_SESSION['csrf_alquiler'] ?? '');
        $enviado = (string) ($_POST['csrf_token'] ?? '');

        if ($actual === '' || !hash_equals($actual, $enviado)) {
            http_response_code(419);
            exit('La sesión del formulario venció. Recargue la página e inténtelo nuevamente.');
        }
    }

    // Listar alquileres: administrador y bibliotecario
    public function index()
    {
        requerir_personal();

        $fecha_desde = $_GET['fecha_desde'] ?? '';
        $fecha_hasta = $_GET['fecha_hasta'] ?? '';
        $busqueda = trim($_GET['busqueda'] ?? '');

        $alquileres = $this->alquilerModel->listarTodos(
            $fecha_desde,
            $fecha_hasta,
            $busqueda
        );

        $this->view('alquileres/index', [
            'titulo_pagina' => 'Gestión de Alquileres',
            'alquileres' => $alquileres,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'busqueda' => $busqueda,
            'csrf_token' => $this->token()
        ]);
    }

    // Mostrar formulario para registrar alquiler
    public function crear()
    {
        requerir_personal();

        $clientes = $this->alquilerModel->listarClientesActivos();
        $libros = $this->alquilerModel->listarLibrosDisponibles();

        $this->view('alquileres/crear', [
            'titulo_pagina' => 'Registrar Alquiler',
            'clientes' => $clientes,
            'libros' => $libros,
            'csrf_token' => $this->token()
        ]);
    }

    // Guardar alquiler
    public function guardar()
    {
        requerir_personal();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('alquileres');
        }

        $this->validarToken();

        try {
            $resultado = $this->alquilerModel->crearAlquiler(
                $_POST['id_usuario'] ?? 0,
                $_POST['id_libro'] ?? 0,
                $_POST['fecha_inicio'] ?? '',
                $_POST['dias_alquiler'] ?? 0,
                $_POST['metodo_pago'] ?? '',
                $_POST['num_operacion'] ?? ''
            );

            $_SESSION['mensaje'] =
                'Alquiler registrado correctamente. N.º ' .
                $resultado['id_alquiler'];

            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Throwable $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        $this->redirect('alquileres');
    }

    // Mostrar detalles de un alquiler
    public function detalle()
    {
        requerir_personal();

        $id = (int) ($_GET['id'] ?? 0);

        $alquiler = $this->alquilerModel->obtenerPorId($id);

        if (!$alquiler) {
            $_SESSION['mensaje'] = 'Alquiler no encontrado.';
            $_SESSION['tipo_mensaje'] = 'danger';
            $this->redirect('alquileres');
        }

        $this->view('alquileres/detalle', [
            'titulo_pagina' => 'Detalle del Alquiler',
            'alquiler' => $alquiler,
            'csrf_token' => $this->token()
        ]);
    }

    // Procesar devolución
    public function devolver()
    {
        requerir_personal();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('alquileres');
        }

        $this->validarToken();

        try {
            $resultado = $this->alquilerModel->procesarDevolucion(
                $_POST['id_alquiler'] ?? 0,
                $_POST['fecha_dev_real'] ?? '',
                ($_POST['cobrar_multa_ahora'] ?? '0') === '1'
            );

            $_SESSION['mensaje'] = 'Devolución registrada correctamente.';

            if ($resultado['dias_atraso'] > 0) {
                $_SESSION['mensaje'] .=
                    ' Días de atraso: ' . $resultado['dias_atraso'] .
                    '. Multa: S/ ' . number_format($resultado['monto_multa'], 2);
            }

            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Throwable $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        $this->redirect('alquileres');
    }

    // Listar multas
    public function multas()
    {
        requerir_personal();

        $multas = $this->alquilerModel->listarMultas();

        $this->view('alquileres/multas', [
            'titulo_pagina' => 'Gestión de Multas',
            'multas' => $multas,
            'csrf_token' => $this->token()
        ]);
    }

    // Cobrar multa
    public function cobrarMulta()
    {
        requerir_personal();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('alquileres/multas');
        }

        $this->validarToken();

        try {
            $id_multa = (int) ($_POST['id_multa'] ?? 0);

            $this->alquilerModel->cobrarMulta($id_multa);

            $_SESSION['mensaje'] = 'Multa cobrada correctamente.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Throwable $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        $this->redirect('alquileres/multas');
    }

    // Alquileres del cliente
    public function misAlquileres()
    {
        requerir_autenticacion();

        $usuario = obtener_usuario_actual();

        if (!$usuario) {
            $this->redirect('login');
        }

        $id_usuario = (int) ($usuario['id'] ?? 0);

        if ($id_usuario <= 0) {
            $this->redirect('login');
        }

        $alquileres = $this->alquilerModel->listarPorCliente(
            $id_usuario
        );

        $this->view('cliente/alquileres', [
            'titulo_pagina' => 'Mis Alquileres',
            'alquileres' => $alquileres
        ]);
    }
    // Mostrar formulario de solicitud de alquiler para clientes
    public function solicitar()
    {
        requerir_autenticacion();

    $usuario = obtener_usuario_actual();

    if (!$usuario) {
        $this->redirect('login');
    }

    // Obtener el ID del cliente desde la sesión
    $id_usuario = (int) ($usuario['id'] ?? 0);

    if ($id_usuario <= 0) {
        $this->redirect('login');
    }

    // Obtener libros disponibles
    $libros = $this->alquilerModel->listarLibrosDisponibles();

    // Si se seleccionó un libro desde el catálogo
    $id_libro = (int) ($_GET['id_libro'] ?? 0);

    $this->view('cliente/solicitar_alquiler', [
        'titulo_pagina' => 'Solicitar Alquiler',
        'libros' => $libros,
        'id_libro' => $id_libro,
        'csrf_token' => $this->token()
        ]);
    }


    // Procesar solicitud de alquiler del cliente
    public function guardarSolicitud()
    {
        requerir_autenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('catalogo');
        }

        $this->validarToken();

        $usuario = obtener_usuario_actual();

        if (!$usuario) {
            $this->redirect('login');
        }

        // El ID del cliente siempre se obtiene de la sesión.
        // No se acepta el id_usuario enviado desde el formulario.
        $id_usuario = (int) ($usuario['id'] ?? 0);
        if ($id_usuario <= 0) {
            $this->redirect('login');
        }

        try {
            $resultado = $this->alquilerModel->crearAlquiler(
                $id_usuario,
                $_POST['id_libro'] ?? 0,
                $_POST['fecha_inicio'] ?? '',
                $_POST['dias_alquiler'] ?? 0,
                $_POST['metodo_pago'] ?? '',
                $_POST['num_operacion'] ?? ''
            );

            $_SESSION['mensaje'] = 'Alquiler registrado correctamente. N.º ' . $resultado['id_alquiler'];
            $_SESSION['tipo_mensaje'] = 'success';

            $this->redirect('mis-alquileres');

        } catch (Throwable $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';

            $this->redirect('solicitar-alquiler?id_libro=' . (int) ($_POST['id_libro'] ?? 0));
        }
    }
        public function misMultas() {

        requerir_autenticacion();

        $usuario = obtener_usuario_actual();
        $id_usuario = (int) $usuario['id'];

        $alquileres = $this->alquilerModel->listarPorCliente($id_usuario);

        $multas = array_filter($alquileres, function ($alquiler) {
            return isset($alquiler['monto_multa'])
                && (float) $alquiler['monto_multa'] > 0;
        });

        $this->view('cliente/multas', [
            'titulo_pagina' => 'Mis Multas',
            'multas'        => $multas
        ]);
    }
}
