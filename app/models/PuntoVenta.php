
<?php

require_once ROOT_PATH . '/app/models/Database.php';

class PuntoVenta
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    /**
     * Listar libros disponibles para realizar ventas.
     */
    public function listarLibrosDisponibles()
    {
        $sql = "SELECT id_libro, titulo, isbn, precio_venta, stock
                FROM libros
                WHERE stock > 0
                  AND estado = 'DISPONIBLE'
                ORDER BY titulo ASC";

        $resultado = $this->db->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Listar clientes activos.
     */
    public function listarClientesActivos()
    {
        $sql = "SELECT id_usuario, dni, nombre, apellido,
                       email, telefono
                FROM usuarios
                WHERE id_rol = 2
                  AND estado = 'ACTIVO'
                ORDER BY dni = '00000000' DESC, nombre ASC";

        $resultado = $this->db->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Buscar un cliente por DNI.
     */
    public function obtenerClientePorDni($dni)
    {
        $sql = "SELECT id_usuario, dni, nombre, apellido
                FROM usuarios
                WHERE dni = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $dni);
        $stmt->execute();

        $cliente = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return $cliente ?: null;
    }

    /**
     * Registrar un cliente nuevo.
     */
    public function registrarCliente(
        $dni,
        $nombre,
        $apellido,
        $telefono
    ) {
        $dni = trim($dni);
        $nombre = trim($nombre);
        $apellido = trim($apellido);
        $telefono = trim($telefono);

        if ($dni === '' || $nombre === '' || $apellido === '') {
            throw new RuntimeException(
                'Complete el DNI, nombre y apellido del cliente.'
            );
        }

        // Evitar registrar dos clientes con el mismo DNI.
        $existente = $this->obtenerClientePorDni($dni);

        if ($existente) {
            throw new RuntimeException(
                'Ya existe un usuario registrado con ese DNI.'
            );
        }

        $email = $dni . '@cliente.com';

        $password = password_hash($dni, PASSWORD_DEFAULT);

        $id_rol = 2;
        $estado = 'ACTIVO';

        $sql = "INSERT INTO usuarios
                (id_rol, dni, nombre, apellido, email,
                 telefono, password, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        $stmt->bind_param(
            'isssssss',
            $id_rol,
            $dni,
            $nombre,
            $apellido,
            $email,
            $telefono,
            $password,
            $estado
        );

        $stmt->execute();

        $id_usuario = $this->db->insert_id;

        $stmt->close();

        return $id_usuario;
    }
}
