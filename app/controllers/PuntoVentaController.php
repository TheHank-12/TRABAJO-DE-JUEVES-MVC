
<?php

class PuntoVentaController extends Controller
{
    private $puntoVentaModel;
    private $carritoModel;

    public function __construct()
    {
        requerir_personal();

        $this->puntoVentaModel = new PuntoVenta();
        $this->carritoModel = new Carrito();
    }

    /**
     * Mostrar formulario del punto de venta.
     */
    public function index()
    {
        $libros = $this->puntoVentaModel->listarLibrosDisponibles();
        $clientes = $this->puntoVentaModel->listarClientesActivos();

        $this->view('ventas/crear', [
            'titulo_pagina' => 'Nueva Venta',
            'libros'        => $libros,
            'clientes'      => $clientes
        ]);
    }

    /**
     * Registrar una venta desde el punto de venta.
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'public/ventas/crear');
            exit();
        }

        // Controla si este método inició una transacción.
        $transaccionIniciada = false;

        try {

            // 1. Validar token CSRF.
            $token = $_POST['csrf_token'] ?? '';

            if (!validar_token_csrf($token)) {
                throw new RuntimeException(
                    'La sesión del formulario expiró o el token no es válido. Recargue la página e intente nuevamente.'
                );
            }

            // 2. Obtener información del formulario.
            $tipo_cliente = $_POST['tipo_cliente'] ?? 'EXISTENTE';

            $metodo_pago = strtoupper(
                trim($_POST['metodo_pago'] ?? '')
            );

            $num_operacion = trim(
                $_POST['num_operacion'] ?? ''
            );

            $id_libro = filter_var(
                $_POST['id_libro'] ?? null,
                FILTER_VALIDATE_INT
            );

            $cantidad = filter_var(
                $_POST['cantidad'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id_libro || !$cantidad || $cantidad <= 0) {
                throw new RuntimeException(
                    'Seleccione un libro y una cantidad válida.'
                );
            }

            // 3. Iniciar una única transacción compartida.
            $db = Database::conectar();
            $db->begin_transaction();

            $transaccionIniciada = true;

            // 4. Identificar o registrar al cliente.
            if ($tipo_cliente === 'NUEVO') {

                $dni = trim($_POST['dni'] ?? '');
                $nombre = trim($_POST['nombre'] ?? '');
                $apellido = trim($_POST['apellido'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');

                $id_usuario = $this->puntoVentaModel->registrarCliente(
                    $dni,
                    $nombre,
                    $apellido,
                    $telefono
                );

            } elseif ($tipo_cliente === 'EXISTENTE') {

                $id_usuario = filter_var(
                    $_POST['id_usuario'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$id_usuario || $id_usuario <= 0) {
                    throw new RuntimeException(
                        'Seleccione un cliente válido.'
                    );
                }

            } else {

                throw new RuntimeException(
                    'El tipo de cliente seleccionado no es válido.'
                );
            }

            // 5. Preparar el carrito temporal.
            $carritoTemporal = [
                $id_libro => $cantidad
            ];

            // 6. Procesar la venta dentro de la transacción externa.
            $id_venta = $this->carritoModel->procesarCompra(
                $id_usuario,
                $metodo_pago,
                $num_operacion,
                $carritoTemporal,
                true
            );

            // 7. Confirmar todas las operaciones juntas.
            $db->commit();
            $transaccionIniciada = false;

            // 8. Redirigir al comprobante.
            $_SESSION['mensaje'] = 'Venta registrada correctamente.';
            $_SESSION['tipo_mensaje'] = 'success';

            header(
                'Location: ' . BASE_URL .
                'public/ventas/comprobante?id=' . $id_venta
            );
            exit();

        } catch (Throwable $e) {

            // Si algo falla, revertir cliente, venta,
            // detalle, stock y Kárdex.
            if ($transaccionIniciada) {
                try {
                    Database::conectar()->rollback();
                } catch (Throwable $errorRollback) {
                    error_log(
                        'Error al revertir la transacción del POS: ' .
                        $errorRollback->getMessage()
                    );
                }
            }

            error_log(
                'Error al registrar venta POS: ' . $e->getMessage()
            );

            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';

            header('Location: ' . BASE_URL . 'public/ventas/crear');
            exit();
        }
    }
}
