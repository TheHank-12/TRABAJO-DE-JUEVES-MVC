
<?php

class CarritoController extends Controller
{
    private $carritoModel;

    public function __construct()
    {
        $this->carritoModel = new Carrito();
        inicializar_carrito();
    }

    /**
     * Mostrar el carrito.
     */
    public function index()
    {
        $items = [];
        $subtotal = 0;

        foreach ($_SESSION['carrito'] as $id_libro => $cantidad) {

            $id_libro = (int) $id_libro;
            $cantidad = (int) $cantidad;

            if ($id_libro <= 0 || $cantidad <= 0) {
                unset($_SESSION['carrito'][$id_libro]);
                continue;
            }

            $libro = $this->carritoModel->obtenerLibro($id_libro);

            if (!$libro) {
                unset($_SESSION['carrito'][$id_libro]);
                continue;
            }

            $precio = (float) $libro['precio_venta'];
            $importe = round($precio * $cantidad, 2);

            $subtotal += $importe;

            $items[] = [
                'id_libro' => $id_libro,
                'titulo' => $libro['titulo'],
                'isbn' => $libro['isbn'],
                'portada' => $libro['portada'],
                'precio' => $precio,
                'stock' => (int) $libro['stock'],
                'cantidad' => $cantidad,
                'subtotal' => $importe
            ];
        }

        $total = round($subtotal, 2);
        $subtotal_sin_igv = round($total / 1.18, 2);
        $igv = round($total - $subtotal_sin_igv, 2);

        $clientes = [];

        if (es_admin()) {
            $clientes = $this->carritoModel->listarClientes();
        }

        $this->view('carrito/index', [
            'titulo_pagina' => 'Carrito de Compras',
            'items' => $items,
            'subtotal' => $subtotal_sin_igv,
            'igv' => $igv,
            'total' => $total,
            'clientes' => $clientes,
            'csrf_token' => generar_token_csrf()
        ]);
    }

    /**
     * Agregar un libro al carrito.
     */
    public function agregar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/');
        }

        if (!validar_token_csrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['mensaje'] = 'Solicitud no válida. Actualice la página e inténtelo nuevamente.';
            $_SESSION['tipo_mensaje'] = 'danger';
            $this->redirect('public/');
        }

        $id_libro = filter_var(
            $_POST['id_libro'] ?? null,
            FILTER_VALIDATE_INT
        );

        $cantidad = filter_var(
            $_POST['cantidad'] ?? 1,
            FILTER_VALIDATE_INT
        );

        if (!$id_libro || $id_libro <= 0 || !$cantidad || $cantidad <= 0) {
            $_SESSION['mensaje'] = 'Seleccione un libro y una cantidad válidos.';
            $_SESSION['tipo_mensaje'] = 'warning';
            $this->redirect('public/');
        }

        $libro = $this->carritoModel->obtenerLibro($id_libro);

        if (
            !$libro ||
            $libro['estado'] !== 'DISPONIBLE' ||
            (int) $libro['stock'] <= 0
        ) {
            $_SESSION['mensaje'] = 'El libro no está disponible.';
            $_SESSION['tipo_mensaje'] = 'danger';
            $this->redirect('public/');
        }

        $cantidad_actual = (int) (
            $_SESSION['carrito'][$id_libro] ?? 0
        );

        $cantidad_nueva = $cantidad_actual + $cantidad;

        if ($cantidad_nueva > (int) $libro['stock']) {
            $_SESSION['mensaje'] =
                'Stock insuficiente. Disponible: ' . $libro['stock'];
            $_SESSION['tipo_mensaje'] = 'warning';

            $this->redirect('public/');
        }

        // Se almacena únicamente la cantidad.
        $_SESSION['carrito'][$id_libro] = $cantidad_nueva;

        $_SESSION['mensaje'] =
            'Libro agregado al carrito correctamente.';

        $_SESSION['tipo_mensaje'] = 'success';

        $this->redirect('public/carrito');
    }

    /**
     * Actualizar la cantidad de un libro.
     */
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/carrito');
        }

        if (!validar_token_csrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['mensaje'] = 'Token de seguridad inválido.';
            $_SESSION['tipo_mensaje'] = 'danger';
            $this->redirect('public/carrito');
        }

        $id_libro = filter_var(
            $_POST['id_libro'] ?? null,
            FILTER_VALIDATE_INT
        );

        $cantidad = filter_var(
            $_POST['cantidad'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id_libro || $id_libro <= 0 ||
            !isset($_SESSION['carrito'][$id_libro])) {

            $_SESSION['mensaje'] = 'Producto no válido.';
            $_SESSION['tipo_mensaje'] = 'warning';
            $this->redirect('public/carrito');
        }

        if ($cantidad === false || $cantidad < 0) {
            $_SESSION['mensaje'] = 'Cantidad no válida.';
            $_SESSION['tipo_mensaje'] = 'warning';
            $this->redirect('public/carrito');
        }

        if ($cantidad === 0) {
            unset($_SESSION['carrito'][$id_libro]);

            $_SESSION['mensaje'] = 'Libro eliminado del carrito.';
            $_SESSION['tipo_mensaje'] = 'info';

            $this->redirect('public/carrito');
        }

        $libro = $this->carritoModel->obtenerLibro($id_libro);

        if (
            !$libro ||
            $libro['estado'] !== 'DISPONIBLE' ||
            $cantidad > (int) $libro['stock']
        ) {
            $_SESSION['mensaje'] = 'La cantidad supera el stock disponible.';
            $_SESSION['tipo_mensaje'] = 'warning';
            $this->redirect('public/carrito');
        }

        $_SESSION['carrito'][$id_libro] = $cantidad;

        $_SESSION['mensaje'] = 'Cantidad actualizada correctamente.';
        $_SESSION['tipo_mensaje'] = 'success';

        $this->redirect('public/carrito');
    }

    /**
     * Eliminar un libro específico.
     */
    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/carrito');
        }

        if (!validar_token_csrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['mensaje'] = 'Token de seguridad inválido.';
            $_SESSION['tipo_mensaje'] = 'danger';
            $this->redirect('public/carrito');
        }

        $id_libro = filter_var(
            $_POST['id_libro'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($id_libro && $id_libro > 0) {
            unset($_SESSION['carrito'][$id_libro]);
            $_SESSION['mensaje'] = 'Libro eliminado del carrito.';
            $_SESSION['tipo_mensaje'] = 'info';
        }

        $this->redirect('public/carrito');
    }

    /**
     * Vaciar todo el carrito.
     */
    public function vaciar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/carrito');
        }

        if (!validar_token_csrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['mensaje'] = 'Token de seguridad inválido.';
            $_SESSION['tipo_mensaje'] = 'danger';
            $this->redirect('public/carrito');
        }

        $_SESSION['carrito'] = [];

        $_SESSION['mensaje'] = 'El carrito ha sido vaciado.';
        $_SESSION['tipo_mensaje'] = 'info';

        $this->redirect('public/carrito');
    }

    /**
     * Procesar la compra del carrito.
     */
    public function procesar()
    {
        requerir_autenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/carrito');
        }

        if (!validar_token_csrf($_POST['csrf_token'] ?? '')) {
            $_SESSION['mensaje'] = 'Token de seguridad inválido.';
            $_SESSION['tipo_mensaje'] = 'danger';
            $this->redirect('public/carrito');
        }

        if (empty($_SESSION['carrito'])) {
            $_SESSION['mensaje'] = 'El carrito está vacío.';
            $_SESSION['tipo_mensaje'] = 'warning';
            $this->redirect('public/carrito');
        }

        // El cliente solo puede comprar para su propia cuenta.
        if (es_admin()) {
            $id_usuario = filter_var(
                $_POST['id_usuario_cliente'] ?? null,
                FILTER_VALIDATE_INT
            );
        } elseif (!es_personal()) {
            $id_usuario = (int) $_SESSION['usuario_id'];
        } else {
            // El bibliotecario utiliza el módulo de ventas de mostrador.
            $_SESSION['mensaje'] =
                'Utilice el módulo de ventas para registrar operaciones de mostrador.';
            $_SESSION['tipo_mensaje'] = 'warning';
            $this->redirect('public/');
        }

        if (!$id_usuario || $id_usuario <= 0) {
            $_SESSION['mensaje'] = 'Seleccione un cliente válido.';
            $_SESSION['tipo_mensaje'] = 'warning';
            $this->redirect('public/carrito');
        }

        $metodo_pago = $_POST['metodo_pago'] ?? 'EFECTIVO';

        $num_operacion = trim(
            $_POST['num_operacion'] ?? ''
        );

        try {

            $id_venta = $this->carritoModel->procesarCompra(
                $id_usuario,
                $metodo_pago,
                $num_operacion,
                $_SESSION['carrito']
            );

            $_SESSION['carrito'] = [];

            $_SESSION['mensaje'] =
                'Compra registrada correctamente. Número de venta: ' .
                $id_venta;

            $_SESSION['tipo_mensaje'] = 'success';

            $_SESSION['ultima_venta_id'] = $id_venta;

            $this->redirect('public/carrito');

        } catch (Throwable $e) {

            error_log('Error al procesar compra: ' . $e->getMessage());

            $_SESSION['mensaje'] = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'No fue posible procesar la compra. Inténtelo nuevamente.';

            $_SESSION['tipo_mensaje'] = 'danger';

            $this->redirect('public/carrito');
        }
    }
}
