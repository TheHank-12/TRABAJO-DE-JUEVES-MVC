<?php

class Libro
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = Database::conectar();
    }

    public function obtenerDisponiblePorId(int $idLibro): ?array
    {
        $sql = "SELECT id_libro, titulo, isbn, precio_venta, stock, portada
                FROM libros
                WHERE id_libro = ? AND estado = 'DISPONIBLE'
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $idLibro);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $libro = $resultado ? $resultado->fetch_assoc() : null;
        $stmt->close();

        return $libro ?: null;
    }

    public function obtenerStockYTituloPorId(int $idLibro): ?array
    {
        $sql = "SELECT stock, titulo
                FROM libros
                WHERE id_libro = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $idLibro);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $libro = $resultado ? $resultado->fetch_assoc() : null;
        $stmt->close();

        return $libro ?: null;
    }

    public function obtenerPorId(int $idLibro): ?array
    {
        $sql = "SELECT id_libro, titulo, isbn, precio_venta, stock, portada
                FROM libros
                WHERE id_libro = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $idLibro);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $libro = $resultado ? $resultado->fetch_assoc() : null;
        $stmt->close();

        return $libro ?: null;
    }
}
