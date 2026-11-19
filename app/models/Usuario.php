<?php

class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function buscarPorEmail($email)
    {
        $sql = "SELECT u.*, r.nombre AS rol_nombre
                FROM usuarios u
                INNER JOIN roles r ON r.id_rol = u.id_rol
                WHERE u.email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        return $usuario ?: null;
    }

    public function registrarCliente($datos, $passwordHash)
    {
        // El rol se obtiene por nombre; no se confía en un id enviado desde el formulario.
        $rol = 'Cliente';
        $sql = "INSERT INTO usuarios
                (id_rol, dni, nombre, apellido, email, password, telefono, direccion, estado)
                SELECT id_rol, ?, ?, ?, ?, ?, ?, ?, 'ACTIVO'
                FROM roles WHERE nombre = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'ssssssss',
            $datos['dni'], $datos['nombre'], $datos['apellido'], $datos['email'],
            $passwordHash, $datos['telefono'], $datos['direccion'], $rol
        );
        $stmt->execute();
        $registrado = $stmt->affected_rows === 1;
        $stmt->close();
        return $registrado;
    }
    public function existeDni($dni, $id_excluir = 0)
    {
        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE dni = ? AND id_usuario != ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $dni, $id_excluir);
        $stmt->execute();

        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $existe;
    }

    public function existeEmail($email, $id_excluir = 0)
    {
        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE email = ? AND id_usuario != ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $email, $id_excluir);
        $stmt->execute();

        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $existe;
    }

    // Listar todos los usuarios
    public function listarTodos()
    {
        $sql = "SELECT u.*, r.nombre AS rol_nombre
                FROM usuarios u
                INNER JOIN roles r ON r.id_rol = u.id_rol
                ORDER BY u.id_usuario DESC";

        $resultado = $this->db->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Listar roles disponibles
    public function listarRoles()
    {
        $sql = "SELECT * FROM roles ORDER BY id_rol ASC";
        $resultado = $this->db->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener usuario por ID
    public function obtenerPorId($id_usuario)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios WHERE id_usuario = ? LIMIT 1"
        );

        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();

        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $usuario ?: null;
    }

    // Registrar usuario desde administración
    public function crear($datos, $password_hash)
    {
        $sql = "INSERT INTO usuarios
                (id_rol, dni, nombre, apellido, email, password,
                 telefono, direccion, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        $stmt->bind_param(
            'issssssss',
            $datos['id_rol'],
            $datos['dni'],
            $datos['nombre'],
            $datos['apellido'],
            $datos['email'],
            $password_hash,
            $datos['telefono'],
            $datos['direccion'],
            $datos['estado']
        );

        $stmt->execute();
        $registrado = $stmt->affected_rows === 1;
        $stmt->close();

        return $registrado;
    }

    // Actualizar usuario
    public function actualizar($id_usuario, $datos, $password_hash = null)
    {
        if ($password_hash !== null) {
            $sql = "UPDATE usuarios SET
                    id_rol = ?, dni = ?, nombre = ?, apellido = ?,
                    email = ?, password = ?, telefono = ?,
                    direccion = ?, estado = ?
                    WHERE id_usuario = ?";

            $stmt = $this->db->prepare($sql);

            $stmt->bind_param(
                'issssssssi',
                $datos['id_rol'],
                $datos['dni'],
                $datos['nombre'],
                $datos['apellido'],
                $datos['email'],
                $password_hash,
                $datos['telefono'],
                $datos['direccion'],
                $datos['estado'],
                $id_usuario
            );
        } else {
            $sql = "UPDATE usuarios SET
                    id_rol = ?, dni = ?, nombre = ?, apellido = ?,
                    email = ?, telefono = ?, direccion = ?, estado = ?
                    WHERE id_usuario = ?";

            $stmt = $this->db->prepare($sql);

            $stmt->bind_param(
                'isssssssi',
                $datos['id_rol'],
                $datos['dni'],
                $datos['nombre'],
                $datos['apellido'],
                $datos['email'],
                $datos['telefono'],
                $datos['direccion'],
                $datos['estado'],
                $id_usuario
            );
        }

        $stmt->execute();
        $actualizado = $stmt->affected_rows >= 0;
        $stmt->close();

        return $actualizado;
    }

    // Comprobar alquileres activos
    public function tieneAlquileresActivos($id_usuario)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM alquileres
                WHERE id_usuario = ?
                AND estado IN ('En Alquiler', 'Con Demora')";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();

        $resultado = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int) $resultado['total'] > 0;
    }

    // Cambiar estado
    public function cambiarEstado($id_usuario, $estado)
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios SET estado = ? WHERE id_usuario = ?"
        );

        $stmt->bind_param('si', $estado, $id_usuario);
        $stmt->execute();

        $resultado = $stmt->affected_rows >= 0;
        $stmt->close();

        return $resultado;
    }

    // Eliminar usuario
    public function eliminar($id_usuario)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM usuarios WHERE id_usuario = ?"
        );

        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();

        $eliminado = $stmt->affected_rows === 1;
        $stmt->close();

        return $eliminado;
    }

}
