<?php
require_once ROOT_PATH . '/app/models/Database.php';

class Reporte {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function obtenerResumenGeneral() {
        $resVentas = $this->db->query("SELECT COUNT(*) AS total_ventas, COALESCE(SUM(total), 0) AS ingresos_ventas FROM ventas");
        $ventas = $resVentas ? $resVentas->fetch_assoc() : ['total_ventas' => 0, 'ingresos_ventas' => 0];

        $resAlq = $this->db->query("SELECT COUNT(*) AS total_alquileres, COALESCE(SUM(monto_alquiler), 0) AS ingresos_alquileres FROM alquileres");
        $alquileres = $resAlq ? $resAlq->fetch_assoc() : ['total_alquileres' => 0, 'ingresos_alquileres' => 0];

        $resMultas = $this->db->query("SELECT COUNT(*) AS multas_pendientes, COALESCE(SUM(monto_total), 0) AS monto_pendiente FROM multas WHERE estado = 'Pendiente'");
        $multas = $resMultas ? $resMultas->fetch_assoc() : ['multas_pendientes' => 0, 'monto_pendiente' => 0];

        $resStock = $this->db->query("SELECT COUNT(*) AS bajo_stock FROM libros WHERE stock <= 3");
        $stock = $resStock ? $resStock->fetch_assoc() : ['bajo_stock' => 0];

        return [
            'total_ventas'        => (int)$ventas['total_ventas'],
            'ingresos_ventas'     => (float)$ventas['ingresos_ventas'],
            'total_alquileres'    => (int)$alquileres['total_alquileres'],
            'ingresos_alquileres' => (float)$alquileres['ingresos_alquileres'],
            'multas_pendientes'   => (int)$multas['multas_pendientes'],
            'monto_pendiente'     => (float)$multas['monto_pendiente'],
            'bajo_stock'          => (int)$stock['bajo_stock']
        ];
    }

    public function obtenerVentas($limite = 0) {
        $sql = "SELECT v.*, u.nombre, u.apellido, u.dni 
                FROM ventas v 
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario 
                ORDER BY v.id_venta DESC";
        if ($limite > 0) {
            $sql .= " LIMIT " . intval($limite);
        }
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerAlquileres($limite = 0) {
        $sql = "SELECT a.*, u.nombre, u.apellido, l.titulo 
                FROM alquileres a 
                INNER JOIN usuarios u ON a.id_usuario = u.id_usuario 
                INNER JOIN libros l ON a.id_libro = l.id_libro 
                ORDER BY a.id_alquiler DESC";
        if ($limite > 0) {
            $sql .= " LIMIT " . intval($limite);
        }
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerLibrosBajoStock() {
        $sql = "SELECT id_libro, titulo, isbn, stock, estado FROM libros WHERE stock <= 3 ORDER BY stock ASC, titulo ASC";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerMultas() {
        $sql = "SELECT m.*, a.fecha_dev_esperada, a.fecha_dev_real, 
                       u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.dni AS usuario_dni,
                       l.titulo AS libro_titulo
                FROM multas m
                INNER JOIN alquileres a ON m.id_alquiler = a.id_alquiler
                INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                INNER JOIN libros l ON a.id_libro = l.id_libro
                ORDER BY m.id_multa DESC";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    
    public function cobrarMulta($id_multa) {

        if ($id_multa <= 0) {
            return [
                'exito' => false,
                'mensaje' => 'Identificador de multa no válido.'
            ];
        }

        try {
            $this->db->begin_transaction();

            $stmt = $this->db->prepare(
                "SELECT id_multa, estado
                FROM multas
                WHERE id_multa = ?
                LIMIT 1
                FOR UPDATE"
            );

            $stmt->bind_param('i', $id_multa);
            $stmt->execute();

            $res = $stmt->get_result();
            $multa = $res->fetch_assoc();

            if (!$multa) {
                $this->db->rollback();

                return [
                    'exito' => false,
                    'mensaje' => 'La multa seleccionada no existe.'
                ];
            }

            if ($multa['estado'] === 'Pagada') {
                $this->db->rollback();

                return [
                    'exito' => false,
                    'mensaje' => 'Esta multa ya fue pagada anteriormente.'
                ];
            }

            $fecha_pago = date('Y-m-d H:i:s');

            $stmtActualizar = $this->db->prepare(
                "UPDATE multas
                SET estado = 'Pagada', fecha_pago = ?
                WHERE id_multa = ? AND estado = 'Pendiente'"
            );

            $stmtActualizar->bind_param('si', $fecha_pago, $id_multa);
            $stmtActualizar->execute();

            if ($stmtActualizar->affected_rows !== 1) {
                throw new Exception('No se pudo actualizar el estado de la multa.');
            }

            $this->db->commit();

            return [
                'exito' => true,
                'mensaje' => 'Multa cobrada exitosamente.'
            ];

        } catch (Throwable $e) {

            $this->db->rollback();

            error_log('Error al cobrar multa: ' . $e->getMessage());

            return [
                'exito' => false,
                'mensaje' => 'Ocurrió un error al registrar el pago de la multa.'
            ];
        }
    }

}