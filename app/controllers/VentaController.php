
<?php

class VentaController extends Controller {

    private $ventaModel;

    public function __construct() {
        $this->ventaModel = new Venta();
    }

    /**
     * Mostrar el historial de ventas.
     * Solo Administrador y Bibliotecario.
     */
    public function index() {

        requerir_personal();

        $busqueda = isset($_GET['q'])
            ? trim($_GET['q'])
            : '';

        $ventas = $this->ventaModel->listarTodas($busqueda);

        $this->view('ventas/index', [
            'titulo_pagina' => 'Historial de Ventas',
            'ventas'        => $ventas,
            'busqueda'      => $busqueda
        ]);
    }

    /**
     * Mostrar el comprobante de una venta.
     * Personal o el cliente propietario de la compra.
     */
    public function comprobante() {

        requerir_autenticacion();

        $id_venta = isset($_GET['id'])
            ? filter_var($_GET['id'], FILTER_VALIDATE_INT)
            : false;

        if (!$id_venta || $id_venta <= 0) {

            $_SESSION['mensaje'] = 'Comprobante de venta no encontrado.';
            $_SESSION['tipo_mensaje'] = 'danger';

            header('Location: ' . BASE_URL . 'public/catalogo');
            exit();
        }

        $venta = $this->ventaModel->obtenerPorId($id_venta);

        if (!$venta) {

            $_SESSION['mensaje'] = 'Comprobante de venta no encontrado.';
            $_SESSION['tipo_mensaje'] = 'danger';

            header('Location: ' . BASE_URL . 'public/catalogo');
            exit();
        }

        // Verificar que el cliente solamente consulte su propia compra.
        if (
            !es_personal() &&
            (int) $venta['id_usuario'] !== (int) $_SESSION['usuario_id']
        ) {

            $_SESSION['mensaje'] = 'Acceso denegado a este comprobante de venta.';
            $_SESSION['tipo_mensaje'] = 'danger';

            header('Location: ' . BASE_URL . 'public/catalogo');
            exit();
        }

        $detalles = $this->ventaModel->listarDetalles($id_venta);

        $this->view('ventas/comprobante', [
            'titulo_pagina' => 'Comprobante de Venta',
            'venta'         => $venta,
            'detalles'      => $detalles
        ]);
    }
    /**
     * Mostrar las compras realizadas por el cliente.
     * Cada cliente solamente puede consultar sus propias compras.
     */
    public function misCompras() {

        requerir_autenticacion();

        $usuario = obtener_usuario_actual();
        $id_usuario = (int) $usuario['id'];

        $compras = $this->ventaModel->listarPorCliente($id_usuario);

        $this->view('cliente/compras', [
            'titulo_pagina' => 'Mis Compras',
            'compras'       => $compras
        ]);
    }
}
