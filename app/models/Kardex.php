
<?php

require_once ROOT_PATH . '/app/models/Database.php';

class Kardex {

    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    // Obtener los libros disponibles para el formulario.
    public function obtenerLibros() {

        $sql = "SELECT id_libro, titulo, isbn, stock
                FROM libros
                ORDER BY titulo ASC";

        $resultado = $this->db->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    // Listar movimientos con filtros opcionales.
    public function listar($filtro_libro = 0, $filtro_tipo = '') {

        $sql = "SELECT
                    k.*,
                    l.titulo AS libro_titulo,
                    l.isbn
                FROM kardex k
                INNER JOIN libros l ON k.id_libro = l.id_libro
                WHERE 1=1";

        $tipos = '';
        $parametros = [];

        if ($filtro_libro > 0) {
            $sql .= " AND k.id_libro = ?";
            $tipos .= 'i';
            $parametros[] = $filtro_libro;
        }

        if (!empty($filtro_tipo)) {
            $sql .= " AND k.tipo_movimiento = ?";
            $tipos .= 's';
            $parametros[] = $filtro_tipo;
        }

        $sql .= " ORDER BY k.fecha_movimiento DESC, k.id_kardex DESC";

        $stmt = $this->db->prepare($sql);

        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
        }

        $stmt->execute();

        $resultado = $stmt->get_result();

        $movimientos = $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];

        $stmt->close();

        return $movimientos;
    }

    // Registrar movimiento y actualizar el inventario.
    public function registrarMovimiento(
        $id_libro,
        $tipo_movimiento,
        $cantidad,
        $motivo
    ) {

        // 1. Validar los datos.
        $id_libro = (int)$id_libro;
        $cantidad = (int)$cantidad;
        $tipo_movimiento = trim($tipo_movimiento);
        $motivo = trim($motivo);

        $tiposPermitidos = [
            'ENTRADA',
            'SALIDA_VENTA',
            'SALIDA_ALQUILER',
            'DEVOLUCION',
            'AJUSTE'
        ];

        if ($id_libro <= 0 || $cantidad <= 0) {
            return [
                'exito' => false,
                'mensaje' => 'El libro y la cantidad deben ser válidos.'
            ];
        }

        if (!in_array($tipo_movimiento, $tiposPermitidos, true)) {
            return [
                'exito' => false,
                'mensaje' => 'El tipo de movimiento seleccionado no es válido.'
            ];
        }

        // 2. Iniciar transacción.
        try {

            $this->db->begin_transaction();

            // Bloquear el registro del libro mientras se procesa
            // el movimiento para evitar inconsistencias de stock.
            $stmtLib = $this->db->prepare(
                "SELECT stock, titulo, estado
                 FROM libros
                 WHERE id_libro = ?
                 LIMIT 1
                 FOR UPDATE"
            );

            $stmtLib->bind_param('i', $id_libro);
            $stmtLib->execute();

            $resLib = $stmtLib->get_result();

            if ($resLib->num_rows !== 1) {

                $stmtLib->close();
                $this->db->rollback();

                return [
                    'exito' => false,
                    'mensaje' => 'El libro seleccionado no existe.'
                ];
            }

            $libroData = $resLib->fetch_assoc();
            $stmtLib->close();

            $stock_previo = (int)$libroData['stock'];

            // 3. Calcular el nuevo stock.
            $stock_nuevo = $stock_previo;

            if (
                $tipo_movimiento === 'ENTRADA' ||
                $tipo_movimiento === 'DEVOLUCION'
            ) {

                $stock_nuevo = $stock_previo + $cantidad;

            } elseif (
                $tipo_movimiento === 'SALIDA_VENTA' ||
                $tipo_movimiento === 'SALIDA_ALQUILER'
            ) {

                if ($cantidad > $stock_previo) {

                    $this->db->rollback();

                    return [
                        'exito' => false,
                        'mensaje' => "No se puede realizar una salida de $cantidad unidades. El stock actual es de $stock_previo unidades."
                    ];
                }

                $stock_nuevo = $stock_previo - $cantidad;

            } elseif ($tipo_movimiento === 'AJUSTE') {

                // AJUSTE establece el stock total indicado.
                $stock_nuevo = $cantidad;
            }

            // 4. Determinar el estado del libro.
            if ($libroData['estado'] === 'DESCONTINUADO') {
                $estado_libro = 'DESCONTINUADO';
            } else {
                $estado_libro = $stock_nuevo > 0
                    ? 'DISPONIBLE'
                    : 'AGOTADO';
            }

            // 5. Insertar el movimiento en el Kárdex.
            $stmtKardex = $this->db->prepare(
                "INSERT INTO kardex (
                    id_libro,
                    tipo_movimiento,
                    cantidad,
                    stock_anterior,
                    stock_actual,
                    motivo
                ) VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmtKardex->bind_param(
                'isiiis',
                $id_libro,
                $tipo_movimiento,
                $cantidad,
                $stock_previo,
                $stock_nuevo,
                $motivo
            );

            $stmtKardex->execute();
            $stmtKardex->close();

            // 6. Actualizar el stock del libro.
            $stmtStock = $this->db->prepare(
                "UPDATE libros
                 SET stock = ?, estado = ?
                 WHERE id_libro = ?"
            );

            $stmtStock->bind_param(
                'isi',
                $stock_nuevo,
                $estado_libro,
                $id_libro
            );

            $stmtStock->execute();
            $stmtStock->close();

            // 7. Confirmar ambas operaciones.
            $this->db->commit();

            return [
                'exito' => true,
                'mensaje' => "Movimiento de Kárdex registrado y stock actualizado a $stock_nuevo unidades."
            ];

        } catch (Exception $e) {

            // Si ocurre un error, cancelar las operaciones.
            $this->db->rollback();

            error_log(
                'Error en Kárdex: ' . $e->getMessage()
            );

            return [
                'exito' => false,
                'mensaje' => 'Ocurrió un error al procesar el movimiento. No se realizaron cambios.'
            ];
        }
    }
}
