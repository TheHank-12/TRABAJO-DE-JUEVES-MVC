
<?php

require_once ROOT_PATH . '/app/models/Database.php';

class Venta {

    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function listarTodas($busqueda = '') {

        $sql = "SELECT v.*,
                       u.nombre,
                       u.apellido,
                       u.dni,
                       u.email
                FROM ventas v
                INNER JOIN usuarios u
                    ON v.id_usuario = u.id_usuario";

        if ($busqueda !== '') {
            $sql .= " WHERE (
                        v.num_comprobante LIKE ?
                        OR u.dni LIKE ?
                        OR u.nombre LIKE ?
                        OR u.apellido LIKE ?
                      )";
        }

        $sql .= " ORDER BY v.fecha_venta DESC, v.id_venta DESC";

        $stmt = $this->db->prepare($sql);

        if ($busqueda !== '') {
            $filtro = '%' . $busqueda . '%';
            $stmt->bind_param('ssss', $filtro, $filtro, $filtro, $filtro);
        }

        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId($id_venta) {

        $sql = "SELECT v.*,
                       u.nombre,
                       u.apellido,
                       u.dni,
                       u.email,
                       u.direccion,
                       u.telefono
                FROM ventas v
                INNER JOIN usuarios u
                    ON v.id_usuario = u.id_usuario
                WHERE v.id_venta = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id_venta);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function listarDetalles($id_venta) {

        $sql = "SELECT d.*,
                       l.titulo,
                       l.isbn
                FROM ventas_detalle d
                INNER JOIN libros l
                    ON d.id_libro = l.id_libro
                WHERE d.id_venta = ?
                ORDER BY d.id_detalle ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id_venta);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    /**
     * Listar las compras realizadas por un cliente.
     */
    public function listarPorCliente($id_usuario) {

        $sql = "SELECT *
                FROM ventas
                WHERE id_usuario = ?
                ORDER BY fecha_venta DESC, id_venta DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
