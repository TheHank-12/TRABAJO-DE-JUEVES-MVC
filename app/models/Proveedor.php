<?php
require_once ROOT_PATH . '/app/models/Database.php';

class Proveedor {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function listarTodos() {
        $sql = "SELECT p.*, COUNT(l.id_libro) AS total_libros 
                FROM proveedores p 
                LEFT JOIN libros l ON p.id_proveedor = l.id_proveedor 
                GROUP BY p.id_proveedor 
                ORDER BY p.razon_social ASC";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerPorId($id_proveedor) {
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE id_proveedor = ? LIMIT 1");
        $stmt->bind_param('i', $id_proveedor);
        $stmt->execute();
        $res = $stmt->get_result();
        return ($res && $res->num_rows === 1) ? $res->fetch_assoc() : null;
    }

    public function existeRucDni($ruc_dni, $excluir_id = 0) {
        if ($excluir_id > 0) {
            $stmt = $this->db->prepare("SELECT id_proveedor FROM proveedores WHERE ruc_dni = ? AND id_proveedor != ? LIMIT 1");
            $stmt->bind_param('si', $ruc_dni, $excluir_id);
        } else {
            $stmt = $this->db->prepare("SELECT id_proveedor FROM proveedores WHERE ruc_dni = ? LIMIT 1");
            $stmt->bind_param('s', $ruc_dni);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return ($res && $res->num_rows > 0);
    }

    public function crear($datos) {
        $stmt = $this->db->prepare(
            "INSERT INTO proveedores (ruc_dni, razon_social, contacto, telefono, email, direccion) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'ssssss',
            $datos['ruc_dni'],
            $datos['razon_social'],
            $datos['contacto'],
            $datos['telefono'],
            $datos['email'],
            $datos['direccion']
        );
        return $stmt->execute();
    }

    public function actualizar($id_proveedor, $datos) {
        $stmt = $this->db->prepare(
            "UPDATE proveedores 
             SET ruc_dni = ?, razon_social = ?, contacto = ?, telefono = ?, email = ?, direccion = ? 
             WHERE id_proveedor = ?"
        );
        $stmt->bind_param(
            'ssssssi',
            $datos['ruc_dni'],
            $datos['razon_social'],
            $datos['contacto'],
            $datos['telefono'],
            $datos['email'],
            $datos['direccion'],
            $id_proveedor
        );
        return $stmt->execute();
    }

    public function contarLibrosAsociados($id_proveedor) {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM libros WHERE id_proveedor = ?");
        $stmt->bind_param('i', $id_proveedor);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)$row['total'];
    }

    public function eliminar($id_proveedor) {
        $stmt = $this->db->prepare("DELETE FROM proveedores WHERE id_proveedor = ?");
        $stmt->bind_param('i', $id_proveedor);
        return $stmt->execute();
    }
}