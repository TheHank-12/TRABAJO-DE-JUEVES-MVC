<?php
require_once ROOT_PATH . '/app/models/Database.php';

class Resena {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function listarTodas() {
        $sql = "SELECT r.*, l.titulo AS libro_titulo, l.isbn, u.nombre, u.apellido, u.email 
                FROM resenas r 
                INNER JOIN libros l ON r.id_libro = l.id_libro 
                INNER JOIN usuarios u ON r.id_usuario = u.id_usuario 
                ORDER BY r.id_resena DESC";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function guardar($id_libro, $id_usuario, $puntuacion, $comentario) {
        $stmt = $this->db->prepare(
            "INSERT INTO resenas (id_libro, id_usuario, puntuacion, comentario) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('iiis', $id_libro, $id_usuario, $puntuacion, $comentario);
        return $stmt->execute();
    }

    public function eliminar($id_resena) {
        $stmt = $this->db->prepare("DELETE FROM resenas WHERE id_resena = ?");
        $stmt->bind_param('i', $id_resena);
        return $stmt->execute();
    }
}