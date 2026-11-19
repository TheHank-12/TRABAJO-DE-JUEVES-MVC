
<?php

require_once ROOT_PATH . '/app/models/Database.php';

class Alquiler
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    /**
     * Listar alquileres con filtros de fecha y búsqueda.
     */
    public function listarTodos($fecha_desde = '', $fecha_hasta = '', $busqueda = '')
    {
        $sql = "SELECT
                    a.*,
                    u.nombre AS usuario_nombre,
                    u.apellido AS usuario_apellido,
                    u.dni AS usuario_dni,
                    l.titulo AS libro_titulo,
                    l.isbn AS libro_isbn,
                    m.id_multa,
                    m.monto_total AS monto_multa,
                    m.estado AS estado_multa
                FROM alquileres a
                INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                INNER JOIN libros l ON a.id_libro = l.id_libro
                LEFT JOIN multas m ON a.id_alquiler = m.id_alquiler
                WHERE (? = '' OR a.fecha_inicio >= ?)
                  AND (? = '' OR a.fecha_inicio <= ?)
                  AND (
                      ? = ''
                      OR CONCAT_WS(
                          ' ',
                          u.dni,
                          u.nombre,
                          u.apellido,
                          l.titulo
                      ) LIKE CONCAT('%', ?, '%')
                  )
                ORDER BY a.id_alquiler DESC";

        $stmt = $this->db->prepare($sql);

        $stmt->bind_param(
            'ssssss',
            $fecha_desde,
            $fecha_desde,
            $fecha_hasta,
            $fecha_hasta,
            $busqueda,
            $busqueda
        );

        $stmt->execute();

        $resultado = $stmt->get_result();
        $alquileres = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $alquileres;
    }

    /**
     * Listar clientes activos.
     */
    public function listarClientesActivos()
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
     * Listar libros disponibles para alquiler.
     */
    public function listarLibrosDisponibles()
    {
        $sql = "SELECT
                    id_libro,
                    titulo,
                    precio_alquiler,
                    stock,
                    isbn
                FROM libros
                WHERE estado = 'DISPONIBLE'
                  AND stock > 0
                ORDER BY titulo ASC";

        $resultado = $this->db->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener un alquiler por su ID.
     */
    public function obtenerPorId($id_alquiler)
    {
        $sql = "SELECT
                    a.*,
                    u.nombre AS usuario_nombre,
                    u.apellido AS usuario_apellido,
                    u.dni AS usuario_dni,
                    l.titulo AS libro_titulo,
                    l.isbn AS libro_isbn,
                    l.id_libro
                FROM alquileres a
                INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                INNER JOIN libros l ON a.id_libro = l.id_libro
                WHERE a.id_alquiler = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id_alquiler);
        $stmt->execute();

        $alquiler = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return $alquiler ?: null;
    }

    /**
     * Comprobar si un cliente tiene multas pendientes.
     */
    public function tieneMultasPendientes($id_usuario)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM multas m
                INNER JOIN alquileres a
                    ON m.id_alquiler = a.id_alquiler
                WHERE a.id_usuario = ?
                  AND m.estado = 'Pendiente'";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();

        $resultado = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return (int) $resultado['total'] > 0;
    }

    /**
     * Registrar un alquiler.
     *
     * Descuenta una unidad del inventario y registra
     * el movimiento SALIDA_ALQUILER en el Kárdex.
     */
    public function crearAlquiler(
        $id_usuario,
        $id_libro,
        $fecha_inicio,
        $dias_alquiler,
        $metodo_pago,
        $num_operacion = ''
    ) {
        $id_usuario = (int) $id_usuario;
        $id_libro = (int) $id_libro;
        $dias_alquiler = (int) $dias_alquiler;

        $metodo_pago = strtoupper(trim($metodo_pago));
        $num_operacion = trim($num_operacion);

        if ($id_usuario <= 0 || $id_libro <= 0 || $dias_alquiler <= 0) {
            throw new RuntimeException(
                'Los datos del alquiler no son válidos.'
            );
        }

        $fecha = DateTime::createFromFormat('!Y-m-d', $fecha_inicio);

        if (!$fecha || $fecha->format('Y-m-d') !== $fecha_inicio) {
            throw new RuntimeException(
                'La fecha de inicio no es válida.'
            );
        }

        $metodosPermitidos = [
            'EFECTIVO',
            'YAPE',
            'PLIN',
            'TARJETA',
            'TRANSFERENCIA'
        ];

        if (!in_array($metodo_pago, $metodosPermitidos, true)) {
            throw new RuntimeException(
                'El método de pago seleccionado no es válido.'
            );
        }

        $transaccionIniciada = false;

        try {
            $this->db->begin_transaction();
            $transaccionIniciada = true;

            // 1. Comprobar que el cliente exista y esté activo.
            $sqlCliente = "SELECT id_usuario
                           FROM usuarios
                           WHERE id_usuario = ?
                             AND id_rol = 2
                             AND estado = 'ACTIVO'
                           LIMIT 1
                           FOR UPDATE";

            $stmt = $this->db->prepare($sqlCliente);
            $stmt->bind_param('i', $id_usuario);
            $stmt->execute();

            $cliente = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$cliente) {
                throw new RuntimeException(
                    'El cliente seleccionado no existe o está inactivo.'
                );
            }

            // 2. Verificar multas pendientes.
            if ($this->tieneMultasPendientes($id_usuario)) {
                throw new RuntimeException(
                    'No se puede registrar el alquiler: el cliente tiene multas pendientes de pago.'
                );
            }

            // 3. Consultar y bloquear el libro.
            $sqlLibro = "SELECT
                            id_libro,
                            titulo,
                            precio_alquiler,
                            stock,
                            estado
                         FROM libros
                         WHERE id_libro = ?
                         LIMIT 1
                         FOR UPDATE";

            $stmt = $this->db->prepare($sqlLibro);
            $stmt->bind_param('i', $id_libro);
            $stmt->execute();

            $libro = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$libro) {
                throw new RuntimeException(
                    'El libro seleccionado no existe.'
                );
            }

            if (
                $libro['estado'] !== 'DISPONIBLE' ||
                (int) $libro['stock'] <= 0
            ) {
                throw new RuntimeException(
                    'El libro no cuenta con stock disponible.'
                );
            }

            // 4. Calcular fechas y monto.
            $fechaDevolucion = clone $fecha;
            $fechaDevolucion->modify('+' . $dias_alquiler . ' days');

            $fecha_dev_esperada = $fechaDevolucion->format('Y-m-d');

            $monto_alquiler = round(
                (float) $libro['precio_alquiler'] * $dias_alquiler,
                2
            );

            // 5. Registrar el alquiler.
            $sqlAlquiler = "INSERT INTO alquileres
                            (
                                id_usuario,
                                id_libro,
                                fecha_inicio,
                                fecha_dev_esperada,
                                monto_alquiler,
                                metodo_pago,
                                num_operacion,
                                estado
                            )
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'En Alquiler')";

            $stmt = $this->db->prepare($sqlAlquiler);

            $stmt->bind_param(
                'iissdss',
                $id_usuario,
                $id_libro,
                $fecha_inicio,
                $fecha_dev_esperada,
                $monto_alquiler,
                $metodo_pago,
                $num_operacion
            );

            $stmt->execute();

            $id_alquiler = $this->db->insert_id;

            $stmt->close();

            // 6. Descontar una unidad del stock.
            $stock_anterior = (int) $libro['stock'];
            $stock_actual = $stock_anterior - 1;

            $estado = $stock_actual > 0
                ? 'DISPONIBLE'
                : 'AGOTADO';

            $sqlStock = "UPDATE libros
                         SET stock = ?, estado = ?
                         WHERE id_libro = ?";

            $stmt = $this->db->prepare($sqlStock);

            $stmt->bind_param(
                'isi',
                $stock_actual,
                $estado,
                $id_libro
            );

            $stmt->execute();
            $stmt->close();

            // 7. Registrar movimiento en Kárdex.
            $motivo = 'Alquiler registrado por ' .
                $dias_alquiler . ' días';

            $sqlKardex = "INSERT INTO kardex
                          (
                              id_libro,
                              tipo_movimiento,
                              cantidad,
                              stock_anterior,
                              stock_actual,
                              motivo
                          )
                          VALUES (?, 'SALIDA_ALQUILER', 1, ?, ?, ?)";

            $stmt = $this->db->prepare($sqlKardex);

            $stmt->bind_param(
                'iiis',
                $id_libro,
                $stock_anterior,
                $stock_actual,
                $motivo
            );

            $stmt->execute();
            $stmt->close();

            $this->db->commit();
            $transaccionIniciada = false;

            return [
                'id_alquiler' => $id_alquiler,
                'fecha_dev_esperada' => $fecha_dev_esperada,
                'monto_alquiler' => $monto_alquiler
            ];

        } catch (Throwable $e) {

            if ($transaccionIniciada) {
                $this->db->rollback();
            }

            throw $e;
        }
    }

    /**
     * Procesar la devolución de un libro.
     *
     * Actualiza el alquiler, devuelve el libro al stock,
     * registra el Kárdex y genera la multa si existe demora.
     */
    public function procesarDevolucion(
        $id_alquiler,
        $fecha_dev_real,
        $cobrar_multa_ahora = false
    ) {
        $id_alquiler = (int) $id_alquiler;

        $fecha = DateTime::createFromFormat('!Y-m-d', $fecha_dev_real);

        if (
            $id_alquiler <= 0 ||
            !$fecha ||
            $fecha->format('Y-m-d') !== $fecha_dev_real
        ) {
            throw new RuntimeException(
                'Los datos de devolución no son válidos.'
            );
        }

        $transaccionIniciada = false;

        try {
            $this->db->begin_transaction();
            $transaccionIniciada = true;

            // 1. Consultar y bloquear el alquiler.
            $sqlAlquiler = "SELECT
                                id_alquiler,
                                id_libro,
                                fecha_dev_esperada,
                                estado
                            FROM alquileres
                            WHERE id_alquiler = ?
                            LIMIT 1
                            FOR UPDATE";

            $stmt = $this->db->prepare($sqlAlquiler);
            $stmt->bind_param('i', $id_alquiler);
            $stmt->execute();

            $alquiler = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$alquiler) {
                throw new RuntimeException(
                    'El alquiler seleccionado no existe.'
                );
            }

            if ($alquiler['estado'] !== 'En Alquiler') {
                throw new RuntimeException(
                    'Este alquiler ya fue devuelto o finalizado.'
                );
            }

            // 2. Calcular días de atraso.
            $fechaEsperada = new DateTime(
                $alquiler['fecha_dev_esperada']
            );

            $dias_atraso = 0;

            if ($fecha > $fechaEsperada) {
                $dias_atraso = (int) $fechaEsperada
                    ->diff($fecha)
                    ->days;
            }

            $monto_diario = 5.00;
            $monto_total = round(
                $dias_atraso * $monto_diario,
                2
            );

            // 3. Bloquear y consultar stock actual.
            $id_libro = (int) $alquiler['id_libro'];

            $sqlLibro = "SELECT stock
                         FROM libros
                         WHERE id_libro = ?
                         LIMIT 1
                         FOR UPDATE";

            $stmt = $this->db->prepare($sqlLibro);
            $stmt->bind_param('i', $id_libro);
            $stmt->execute();

            $libro = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$libro) {
                throw new RuntimeException(
                    'No se encontró el libro asociado al alquiler.'
                );
            }

            $stock_anterior = (int) $libro['stock'];
            $stock_actual = $stock_anterior + 1;

            // 4. Actualizar el alquiler.
            $sqlActualizarAlquiler = "UPDATE alquileres
                                      SET fecha_dev_real = ?,
                                          estado = 'Devuelto'
                                      WHERE id_alquiler = ?";

            $stmt = $this->db->prepare($sqlActualizarAlquiler);

            $stmt->bind_param(
                'si',
                $fecha_dev_real,
                $id_alquiler
            );

            $stmt->execute();
            $stmt->close();

            // 5. Devolver una unidad al inventario.
            $sqlStock = "UPDATE libros
                         SET stock = ?,
                             estado = 'DISPONIBLE'
                         WHERE id_libro = ?";

            $stmt = $this->db->prepare($sqlStock);

            $stmt->bind_param(
                'ii',
                $stock_actual,
                $id_libro
            );

            $stmt->execute();
            $stmt->close();

            // 6. Registrar movimiento DEVOLUCION.
            $motivo = 'Devolución de alquiler N° ' . $id_alquiler;

            $sqlKardex = "INSERT INTO kardex
                          (
                              id_libro,
                              tipo_movimiento,
                              cantidad,
                              stock_anterior,
                              stock_actual,
                              motivo
                          )
                          VALUES (?, 'DEVOLUCION', 1, ?, ?, ?)";

            $stmt = $this->db->prepare($sqlKardex);

            $stmt->bind_param(
                'iiis',
                $id_libro,
                $stock_anterior,
                $stock_actual,
                $motivo
            );

            $stmt->execute();
            $stmt->close();

            // 7. Registrar multa cuando existe atraso.
            if ($dias_atraso > 0) {

                $estado_multa = $cobrar_multa_ahora
                    ? 'Pagada'
                    : 'Pendiente';

                $fecha_pago = $cobrar_multa_ahora
                    ? date('Y-m-d H:i:s')
                    : null;

                $sqlMulta = "INSERT INTO multas
                             (
                                 id_alquiler,
                                 dias_atraso,
                                 monto_diario,
                                 monto_total,
                                 estado,
                                 fecha_pago
                             )
                             VALUES (?, ?, ?, ?, ?, ?)";

                $stmt = $this->db->prepare($sqlMulta);

                $stmt->bind_param(
                    'iiddss',
                    $id_alquiler,
                    $dias_atraso,
                    $monto_diario,
                    $monto_total,
                    $estado_multa,
                    $fecha_pago
                );

                $stmt->execute();
                $stmt->close();
            }

            $this->db->commit();
            $transaccionIniciada = false;

            return [
                'dias_atraso' => $dias_atraso,
                'monto_multa' => $monto_total,
                'estado_multa' => $dias_atraso > 0
                    ? $estado_multa
                    : null
            ];

        } catch (Throwable $e) {

            if ($transaccionIniciada) {
                $this->db->rollback();
            }

            throw $e;
        }
    }

    /**
     * Listar todas las multas registradas.
     */
    public function listarMultas()
    {
        $sql = "SELECT
                    m.*,
                    a.fecha_dev_esperada,
                    a.fecha_dev_real,
                    u.nombre AS usuario_nombre,
                    u.apellido AS usuario_apellido,
                    u.dni AS usuario_dni,
                    l.titulo AS libro_titulo
                FROM multas m
                INNER JOIN alquileres a
                    ON m.id_alquiler = a.id_alquiler
                INNER JOIN usuarios u
                    ON a.id_usuario = u.id_usuario
                INNER JOIN libros l
                    ON a.id_libro = l.id_libro
                ORDER BY m.id_multa DESC";

        $resultado = $this->db->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Registrar el cobro de una multa pendiente.
     */
    public function cobrarMulta($id_multa)
    {
        $id_multa = (int) $id_multa;

        if ($id_multa <= 0) {
            throw new RuntimeException('La multa seleccionada no es válida.');
        }

        $transaccionIniciada = false;

        try {
            $this->db->begin_transaction();
            $transaccionIniciada = true;

            $sqlMulta = "SELECT id_multa, id_alquiler, estado
                         FROM multas
                         WHERE id_multa = ?
                         LIMIT 1
                         FOR UPDATE";

            $stmt = $this->db->prepare($sqlMulta);
            $stmt->bind_param('i', $id_multa);
            $stmt->execute();

            $multa = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$multa) {
                throw new RuntimeException('La multa no existe.');
            }

            if ($multa['estado'] !== 'Pendiente') {
                throw new RuntimeException(
                    'La multa seleccionada ya fue pagada.'
                );
            }

            $fecha_pago = date('Y-m-d H:i:s');

            $sqlActualizar = "UPDATE multas
                              SET estado = 'Pagada',
                                  fecha_pago = ?
                              WHERE id_multa = ?";

            $stmt = $this->db->prepare($sqlActualizar);

            $stmt->bind_param(
                'si',
                $fecha_pago,
                $id_multa
            );

            $stmt->execute();
            $stmt->close();

            $this->db->commit();
            $transaccionIniciada = false;

            return true;

        } catch (Throwable $e) {

            if ($transaccionIniciada) {
                $this->db->rollback();
            }

            throw $e;
        }
    }

    /**
     * Listar alquileres del cliente autenticado.
     */
    public function listarPorCliente($id_usuario)
    {
        $sql = "SELECT
                    a.*,
                    l.titulo AS libro_titulo,
                    l.isbn AS libro_isbn,
                    l.portada,
                    m.monto_total AS monto_multa,
                    m.estado AS estado_multa
                FROM alquileres a
                INNER JOIN libros l ON a.id_libro = l.id_libro
                LEFT JOIN multas m ON a.id_alquiler = m.id_alquiler
                WHERE a.id_usuario = ?
                ORDER BY a.id_alquiler DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $alquileres = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $alquileres;
    }
    /**
     * Mostrar las multas del cliente autenticado.
     */
    public function misMultas() {

        requerir_autenticacion();

        $usuario = obtener_usuario_actual();
        $id_usuario = (int) $usuario['id'];

        $alquileres = $this->alquilerModel->listarPorCliente($id_usuario);

        // Mostrar únicamente alquileres que tengan una multa registrada.
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
