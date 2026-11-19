
<?php

require_once ROOT_PATH . '/app/models/Database.php';

class Carrito
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    /**
     * Obtener un libro por su ID.
     */
    public function obtenerLibro($id_libro)
    {
        $sql = "SELECT id_libro, titulo, isbn, precio_venta,
                       stock, portada, estado
                FROM libros
                WHERE id_libro = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id_libro);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $libro = $resultado->fetch_assoc();

        $stmt->close();

        return $libro ?: null;
    }

    /**
     * Listar clientes activos para el checkout del administrador.
     */
    public function listarClientes()
    {
        $sql = "SELECT id_usuario, dni, nombre, apellido
                FROM usuarios
                WHERE estado = 'ACTIVO'
                  AND id_rol = 2
                ORDER BY nombre ASC";

        $resultado = $this->db->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Verificar que el cliente exista y esté activo.
     */
    private function clienteValido($id_usuario)
    {
        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE id_usuario = ?
                  AND id_rol = 2
                  AND estado = 'ACTIVO'
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $valido = $resultado->num_rows === 1;

        $stmt->close();

        return $valido;
    }

    /**
     * Procesar una compra completa.
     *
     * Registra:
     * - Cabecera de venta.
     * - Detalle de venta.
     * - Descuento de inventario.
     * - Movimiento de salida en Kárdex.
     *
     * Devuelve el ID de la venta registrada.
     */
    public function procesarCompra(
        $id_usuario,
        $metodo_pago,
        $num_operacion,
        $carrito,
        $transaccionExterna = false
    ) {
        if (!$this->clienteValido($id_usuario)) {
            throw new RuntimeException('El cliente seleccionado no es válido o está inactivo.');
        }

        $metodosPermitidos = [
            'EFECTIVO',
            'YAPE',
            'PLIN',
            'TARJETA',
            'TRANSFERENCIA'
        ];

        if (!in_array($metodo_pago, $metodosPermitidos, true)) {
            throw new RuntimeException('El método de pago seleccionado no es válido.');
        }

        if (!is_array($carrito) || empty($carrito)) {
            throw new RuntimeException('El carrito está vacío.');
        }

        if (!$transaccionExterna) {
            $this->db->begin_transaction();
        }

        try {
            $items = [];
            $total_general = 0;

            // 1. Validar libros, precios y stock.
            foreach ($carrito as $id_libro => $valor) {

                $id_libro = (int) $id_libro;

                // Compatibilidad con el formato antiguo del carrito.
                $cantidad = is_array($valor)
                    ? (int) ($valor['cantidad'] ?? 0)
                    : (int) $valor;

                if ($id_libro <= 0 || $cantidad <= 0) {
                    throw new RuntimeException('Hay productos inválidos en el carrito.');
                }

                $sql = "SELECT id_libro, titulo, precio_venta, stock, estado
                        FROM libros
                        WHERE id_libro = ?
                        LIMIT 1
                        FOR UPDATE";

                $stmt = $this->db->prepare($sql);
                $stmt->bind_param('i', $id_libro);
                $stmt->execute();

                $libro = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$libro) {
                    throw new RuntimeException('Uno de los libros ya no existe.');
                }

                if (
                    $libro['estado'] !== 'DISPONIBLE' ||
                    (int) $libro['stock'] < $cantidad
                ) {
                    throw new RuntimeException(
                        'Stock insuficiente o libro no disponible: ' .
                        $libro['titulo']
                    );
                }

                $precio = (float) $libro['precio_venta'];
                $subtotal_item = round($precio * $cantidad, 2);

                $total_general += $subtotal_item;

                $items[] = [
                    'id_libro' => $id_libro,
                    'titulo' => $libro['titulo'],
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $subtotal_item,
                    'stock_anterior' => (int) $libro['stock']
                ];
            }

            if (empty($items)) {
                throw new RuntimeException('No hay productos válidos para comprar.');
            }

            // 2. Calcular subtotal e IGV.
            $total_general = round($total_general, 2);
            $subtotal = round($total_general / 1.18, 2);
            $igv = round($total_general - $subtotal, 2);

            // 3. Generar número de comprobante.
            $num_comprobante = 'BOL-' .
                date('Ymd') . '-' .
                random_int(10000, 99999);

            // 4. Registrar cabecera de venta.
            $sqlVenta = "INSERT INTO ventas
                        (id_usuario, num_comprobante, subtotal,
                         igv, total, metodo_pago, num_operacion)
                         VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmtVenta = $this->db->prepare($sqlVenta);

            $stmtVenta->bind_param(
                'isdddss',
                $id_usuario,
                $num_comprobante,
                $subtotal,
                $igv,
                $total_general,
                $metodo_pago,
                $num_operacion
            );

            $stmtVenta->execute();

            $id_venta = $this->db->insert_id;

            $stmtVenta->close();

            // 5. Registrar detalles, actualizar stock y Kárdex.
            foreach ($items as $item) {

                $id_libro = $item['id_libro'];
                $cantidad = $item['cantidad'];
                $precio = $item['precio'];
                $subtotal_item = $item['subtotal'];

                $stock_anterior = $item['stock_anterior'];
                $stock_actual = $stock_anterior - $cantidad;

                // Detalle de venta.
                $sqlDetalle = "INSERT INTO ventas_detalle
                              (id_venta, id_libro, cantidad,
                               precio_unitario, subtotal)
                               VALUES (?, ?, ?, ?, ?)";

                $stmtDetalle = $this->db->prepare($sqlDetalle);

                $stmtDetalle->bind_param(
                    'iiidd',
                    $id_venta,
                    $id_libro,
                    $cantidad,
                    $precio,
                    $subtotal_item
                );

                $stmtDetalle->execute();
                $stmtDetalle->close();

                // Actualizar inventario.
                $estado = $stock_actual > 0
                    ? 'DISPONIBLE'
                    : 'AGOTADO';

                $sqlStock = "UPDATE libros
                             SET stock = ?, estado = ?
                             WHERE id_libro = ?";

                $stmtStock = $this->db->prepare($sqlStock);

                $stmtStock->bind_param(
                    'isi',
                    $stock_actual,
                    $estado,
                    $id_libro
                );

                $stmtStock->execute();
                $stmtStock->close();

                // Registrar salida en Kárdex.
                $motivo = 'Venta web #' . $num_comprobante;

                $sqlKardex = "INSERT INTO kardex
                             (id_libro, tipo_movimiento, cantidad,
                              stock_anterior, stock_actual, motivo)
                              VALUES (?, 'SALIDA_VENTA', ?, ?, ?, ?)";

                $stmtKardex = $this->db->prepare($sqlKardex);

                $stmtKardex->bind_param(
                    'iiiis',
                    $id_libro,
                    $cantidad,
                    $stock_anterior,
                    $stock_actual,
                    $motivo
                );

                $stmtKardex->execute();
                $stmtKardex->close();
            }

            // 6. Confirmar toda la operación.
            if (!$transaccionExterna) {
                $this->db->commit();
            }

            return $id_venta;

        } catch (Throwable $e) {

            if (!$transaccionExterna) {
                $this->db->rollback();
            }

            throw $e;
        }
    }
}
