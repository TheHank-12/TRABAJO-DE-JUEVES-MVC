<?php

class CarritoController extends Controller
{
    public function index(): void
    {
        if (function_exists('inicializar_carrito')) {
            inicializar_carrito();
        }

        $carrito = $_SESSION['carrito'] ?? [];$modeloLibro = new Libro();
        $itemsCarrito = [];$subtotalGeneral = 0.00;

        foreach ($carrito as $idLibro =>$itemCarrito) {
            $idLibro  = (int)$idLibro;
            $cantidad = is_array($itemCarrito) ? (int)($itemCarrito['cantidad'] ?? 0) : (int)$itemCarrito;

            if ($idLibro <= 0 ||$cantidad <= 0) {
                continue;
            }

            $libro = $modeloLibro->obtenerPorId($idLibro);
            if ($libro === null) {
                continue;
            }

            $precioUnitario = (float)$libro['precio_venta'];$sub = $precioUnitario * $cantidad;
            $subtotalGeneral +=$sub;

            $itemsCarrito[] = [
                'id_libro'     => (int)$libro['id_libro'],
                'titulo'       => $libro['titulo'],
                'isbn'         => $libro['isbn'],
                'precio_venta' => $precioUnitario,
                'stock'        => (int)$libro['stock'],
                'portada'      => $libro['portada'],
                'cantidad'     => $cantidad,
                'subtotal'     => $sub
            ];
        }

        $igvGeneral   =$subtotalGeneral * 0.18;
        $totalGeneral = $subtotalGeneral +$igvGeneral;

        $esAdmin = function_exists('es_admin') ? es_admin() : false;
        $estaAutenticado = function_exists('esta_autenticado') ? esta_autenticado() : false;
        $clientes = [];

        if ($esAdmin) {$conexion = Database::conectar();
            $resClientes =$conexion->query("SELECT id_usuario, dni, nombre, apellido FROM usuarios WHERE estado = 'ACTIVO' AND id_rol = 2 ORDER BY nombre ASC");
            if ($resClientes) {
                while ($fila =$resClientes->fetch_assoc()) {
                    $clientes[] =$fila;
                }
            }
        }

        $this->view('carrito/index', [
            'items_carrito'    => $itemsCarrito,
            'subtotal_general' => $subtotalGeneral,
            'igv_general'      => $igvGeneral,
            'total_general'    => $totalGeneral,
            'esta_autenticado' => $estaAutenticado,
            'es_admin'         => $esAdmin,
            'usuario_id'       => $_SESSION['usuario_id'] ?? 0, 'clientes'         =>$clientes
        ]);
    }

    public function agregar(): void
    {
        if (function_exists('inicializar_carrito')) {
            inicializar_carrito();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {$idLibro  = isset($_POST['id_libro']) ? (int)$_POST['id_libro'] : 0;
            $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

            if ($idLibro > 0 &&$cantidad > 0) {
                $modeloLibro = new Libro();$libro = $modeloLibro->obtenerDisponiblePorId($idLibro);

                if ($libro !== null) {$cantActual  = isset($_SESSION['carrito'][$idLibro])
                        ? (is_array($_SESSION['carrito'][$idLibro]) ? (int)($_SESSION['carrito'][$idLibro]['cantidad'] ?? 0) : (int)$_SESSION['carrito'][$idLibro])
                        : 0;
                    $cantDeseada = $cantActual +$cantidad;
                    $stockMax    = (int)$libro['stock'];

                    if ($cantDeseada > $stockMax) {$_SESSION['mensaje'] = "No hay suficiente stock para agregar " . $cantDeseada . " unidades de '" . $libro['titulo'] . "'. Stock máximo: " . $stockMax;
                        $_SESSION['tipo_mensaje'] = "warning";
                    } else {
                        $_SESSION['carrito'][$idLibro] = [
                            'id_libro' => (int)$libro['id_libro'],
                            'titulo'   => $libro['titulo'],
                            'isbn'     => $libro['isbn'],
                            'precio'   => (float)$libro['precio_venta'],
                            'portada'  => $libro['portada'],
                            'cantidad' => $cantDeseada
                        ];

                        $_SESSION['mensaje'] = "¡'" . $libro['titulo'] . "' añadido al carrito de compras!";
                        $_SESSION['tipo_mensaje'] = "success";
                    }
                } else {
                    $_SESSION['mensaje'] = "El libro seleccionado no está disponible.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            }
        }

        $this->redirect('/carrito');
    }

    public function actualizar(): void
    {
        if (function_exists('inicializar_carrito')) {
            inicializar_carrito();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {$idLibro       = isset($_POST['id_libro']) ? (int)$_POST['id_libro'] : 0;
            $nuevaCantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;

            if ($idLibro > 0 && isset($_SESSION['carrito'][$idLibro])) {
                if ($nuevaCantidad > 0) {
                    $modeloLibro = new Libro();$libro = $modeloLibro->obtenerStockYTituloPorId($idLibro);

                    if ($libro !== null) {
                        $stockMax = (int)$libro['stock'];
                        if ($nuevaCantidad <=$stockMax) {
                            $cantidadFinal =$nuevaCantidad;
                            $_SESSION['mensaje'] = "Cantidad actualizada correctamente.";
                            $_SESSION['tipo_mensaje'] = "success";
                        } else {
                            $cantidadFinal =$stockMax;
                            $_SESSION['mensaje'] = "Se ajustó al stock máximo disponible (" . $stockMax . " unidades).";
                            $_SESSION['tipo_mensaje'] = "warning";
                        }

                        if (is_array($_SESSION['carrito'][$idLibro])) {$_SESSION['carrito'][$idLibro]['cantidad'] =$cantidadFinal;
                        } else {
                            $_SESSION['carrito'][$idLibro] =$cantidadFinal;
                        }
                    }
                } else {
                    unset($_SESSION['carrito'][$idLibro]);$_SESSION['mensaje'] = "El libro fue removido del carrito.";
                    $_SESSION['tipo_mensaje'] = "info";
                }
            }
        }

        $this->redirect('/carrito');
    }

    public function eliminar(): void
    {
        if (function_exists('inicializar_carrito')) {
            inicializar_carrito();
        }

        if (isset($_GET['vaciar']) &&$_GET['vaciar'] == '1') {
            $_SESSION['carrito'] = [];$_SESSION['mensaje'] = "Se ha vaciado todo el carrito de compras.";
            $_SESSION['tipo_mensaje'] = "info";
            $this->redirect('/carrito');
        }

        $idLibro = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($idLibro > 0 && isset($_SESSION['carrito'][$idLibro])) {$titulo = is_array($_SESSION['carrito'][$idLibro]) && isset($_SESSION['carrito'][$idLibro]['titulo'])
                ? $_SESSION['carrito'][$idLibro]['titulo']
                : 'el libro';
            unset($_SESSION['carrito'][$idLibro]);$_SESSION['mensaje'] = "Se quitó '" . $titulo . "' del carrito.";
            $_SESSION['tipo_mensaje'] = "info";
        }

        $this->redirect('/carrito');
    }

    public function procesar(): void
    {
        if (function_exists('requerir_autenticacion')) {
            requerir_autenticacion();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {$this->redirect('/carrito');
        }

        $carrito =$_SESSION['carrito'] ?? [];
        if (empty($carrito)) {$_SESSION['mensaje'] = "El carrito de compras está vacío.";
            $_SESSION['tipo_mensaje'] = "warning";
            $this->redirect('/');
        }

        $idUsuarioCliente = (!empty($_POST['id_usuario_cliente']))
            ? (int)$_POST['id_usuario_cliente']
            : (int)($_SESSION['usuario_id'] ?? 0);

        $metodoPago   = isset($_POST['metodo_pago']) ? trim((string)$_POST['metodo_pago']) : 'EFECTIVO';$numOperacion = isset($_POST['num_operacion']) ? trim((string)$_POST['num_operacion']) : '';

        if ($idUsuarioCliente <= 0) {$_SESSION['mensaje'] = "Debe seleccionar un cliente válido para procesar la compra.";
            $_SESSION['tipo_mensaje'] = "danger";
            $this->redirect('/carrito');
        }

        $modeloVenta = new Venta();
        $resultado =$modeloVenta->registrarVentaCarrito($idUsuarioCliente,$carrito, $metodoPago,$numOperacion);

        if ($resultado['ok']) {
            unset($_SESSION['carrito']);$_SESSION['mensaje'] = "¡Compra realizada exitosamente! Comprobante registrado Nº " . $resultado['num_comprobante'];$_SESSION['tipo_mensaje'] = "success";
            $this->redirect('/ventas/comprobante?id=' . (int)$resultado['id_venta']);
        } else {
            $_SESSION['mensaje'] = $resultado['error'];$_SESSION['tipo_mensaje'] = "danger";
            $this->redirect('/carrito');
        }
    }
}
