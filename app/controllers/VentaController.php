<?php

class VentaController extends Controller
{
    public function index(): void
    {
        if (function_exists('requerir_personal')) {
            requerir_personal();
        }

        $busqueda = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $modeloVenta = new Venta();$ventas = $modeloVenta->listarVentas($busqueda);

        $this->view('ventas/index', [
            'ventas'   => $ventas,
            'busqueda' => $busqueda
        ]);
    }

    public function crear(): void
    {
        if (function_exists('requerir_personal')) {
            requerir_personal();
        }

        $modeloVenta = new Venta();
        $datosForm =$modeloVenta->obtenerDatosFormularioPos();

        $this->view('ventas/crear', [
            'libros'   => $datosForm['libros'],
            'clientes' => $datosForm['clientes']
        ]);
    }

    public function guardarPos(): void
    {
        if (function_exists('requerir_personal')) {
            requerir_personal();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {$this->redirect('/ventas/crear');
        }

        $modeloVenta = new Venta();$resultado = $modeloVenta->registrarVentaPos($_POST);

        if ($resultado['ok']) {
            $this->redirect('/ventas/comprobante?id=' . (int)$resultado['id_venta']);
        } else {
            $_SESSION['mensaje'] = $resultado['error'];$_SESSION['tipo_mensaje'] = "danger";
            $this->redirect('/ventas/crear');
        }
    }

    public function comprobante(): void
    {
        if (function_exists('requerir_autenticacion')) {
            requerir_autenticacion();
        }

        $idVenta = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $modeloVenta = new Venta();$datos = $modeloVenta->obtenerComprobantePorId($idVenta);

        if ($datos === null) {$_SESSION['mensaje'] = "Comprobante de venta no encontrado.";
            $_SESSION['tipo_mensaje'] = "danger";
            $this->redirect('/');
        }

        $venta = $datos['venta'];$esPersonal = function_exists('es_personal') ? es_personal() : false;
        $usuarioId  = (int)($_SESSION['usuario_id'] ?? 0);

        if (!$esPersonal && (int)$venta['id_usuario'] !== $usuarioId) {$_SESSION['mensaje'] = "Acceso denegado a este comprobante de venta.";
            $_SESSION['tipo_mensaje'] = "danger";
            $this->redirect('/');
        }

        $this->view('ventas/comprobante', [
            'venta'    => $venta,
            'detalles' => $datos['detalles']
        ]);
    }
}
