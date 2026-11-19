
<?php

class UsuarioController extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    // Listar usuarios
    public function index()
    {
        requerir_admin();

        $usuarios = $this->usuarioModel->listarTodos();

        $this->view('usuarios/index', [
            'titulo_pagina' => 'Gestión de Usuarios',
            'usuarios' => $usuarios
        ]);
    }

    // Mostrar formulario de registro
    public function crear()
    {
        requerir_admin();

        $roles = $this->usuarioModel->listarRoles();

        $this->view('usuarios/crear', [
            'titulo_pagina' => 'Registrar Usuario',
            'roles' => $roles
        ]);
    }

    // Guardar nuevo usuario
    public function guardar()
    {
        requerir_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('usuarios');
        }

        $datos = [
            'id_rol' => (int) ($_POST['id_rol'] ?? 0),
            'dni' => trim($_POST['dni'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'estado' => $_POST['estado'] ?? 'ACTIVO'
        ];

        $password = $_POST['password'] ?? '';

        if (
            $datos['id_rol'] <= 0 ||
            $datos['dni'] === '' ||
            $datos['nombre'] === '' ||
            $datos['apellido'] === '' ||
            $datos['email'] === '' ||
            $password === ''
        ) {
            $_SESSION['error'] = 'Completa todos los campos obligatorios.';
            $_SESSION['datos_formulario'] = $datos;
            $this->redirect('usuarios/crear');
        }

        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El correo electrónico no es válido.';
            $_SESSION['datos_formulario'] = $datos;
            $this->redirect('usuarios/crear');
        }

        if ($this->usuarioModel->existeDni($datos['dni'], 0)) {
            $_SESSION['error'] = 'El DNI ya está registrado.';
            $_SESSION['datos_formulario'] = $datos;
            $this->redirect('usuarios/crear');
        }

        if ($this->usuarioModel->existeEmail($datos['email'], 0)) {
            $_SESSION['error'] = 'El correo electrónico ya está registrado.';
            $_SESSION['datos_formulario'] = $datos;
            $this->redirect('usuarios/crear');
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        if ($this->usuarioModel->crear($datos, $passwordHash)) {
            $_SESSION['exito'] = 'Usuario registrado correctamente.';
            $this->redirect('usuarios');
        }

        $_SESSION['error'] = 'No se pudo registrar el usuario.';
        $this->redirect('usuarios/crear');
    }

    // Mostrar formulario de edición
    public function editar()
    {
        requerir_admin();

        $id_usuario = (int) ($_GET['id'] ?? 0);

        $usuario = $this->usuarioModel->obtenerPorId($id_usuario);

        if (!$usuario) {
            $_SESSION['error'] = 'El usuario solicitado no existe.';
            $this->redirect('usuarios');
        }

        $roles = $this->usuarioModel->listarRoles();

        $this->view('usuarios/editar', [
            'titulo_pagina' => 'Editar Usuario',
            'usuario' => $usuario,
            'roles' => $roles
        ]);
    }

    // Actualizar usuario
    public function actualizar()
    {
        requerir_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('usuarios');
        }

        $id_usuario = (int) ($_POST['id_usuario'] ?? 0);

        $usuarioActual = $this->usuarioModel->obtenerPorId($id_usuario);

        if (!$usuarioActual) {
            $_SESSION['error'] = 'El usuario no existe.';
            $this->redirect('usuarios');
        }

        $datos = [
            'id_rol' => (int) ($_POST['id_rol'] ?? 0),
            'dni' => trim($_POST['dni'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'estado' => $_POST['estado'] ?? 'ACTIVO'
        ];

        $password = $_POST['password'] ?? '';

        if (
            $datos['id_rol'] <= 0 ||
            $datos['dni'] === '' ||
            $datos['nombre'] === '' ||
            $datos['apellido'] === '' ||
            $datos['email'] === ''
        ) {
            $_SESSION['error'] = 'Completa todos los campos obligatorios.';
            $this->redirect('usuarios/editar?id=' . $id_usuario);
        }

        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El correo electrónico no es válido.';
            $this->redirect('usuarios/editar?id=' . $id_usuario);
        }

        if ($this->usuarioModel->existeDni($datos['dni'], $id_usuario)) {
            $_SESSION['error'] = 'El DNI ya pertenece a otro usuario.';
            $this->redirect('usuarios/editar?id=' . $id_usuario);
        }

        if ($this->usuarioModel->existeEmail($datos['email'], $id_usuario)) {
            $_SESSION['error'] = 'El correo electrónico ya pertenece a otro usuario.';
            $this->redirect('usuarios/editar?id=' . $id_usuario);
        }

        // Proteger la cuenta del administrador que inició sesión.
        $esPropiaCuenta = (int) $_SESSION['usuario_id'] === $id_usuario;

        if ($esPropiaCuenta) {
            $datos['id_rol'] = (int) $usuarioActual['id_rol'];
            $datos['estado'] = 'ACTIVO';
        }

        $passwordHash = null;

        if ($password !== '') {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($this->usuarioModel->actualizar($id_usuario, $datos, $passwordHash)) {

            if ($esPropiaCuenta) {
                $_SESSION['usuario_nombre'] = $datos['nombre'];
                $_SESSION['usuario_apellido'] = $datos['apellido'];
                $_SESSION['usuario_email'] = $datos['email'];
                $_SESSION['usuario_dni'] = $datos['dni'];
            }

            $_SESSION['exito'] = 'Usuario actualizado correctamente.';
            $this->redirect('usuarios');
        }

        $_SESSION['error'] = 'No se realizaron cambios en el usuario.';
        $this->redirect('usuarios/editar?id=' . $id_usuario);
    }

    // Cambiar estado del usuario
    public function cambiarEstado()
    {
        requerir_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('usuarios');
        }

        $id_usuario = (int) ($_POST['id_usuario'] ?? 0);

        if ($id_usuario === (int) $_SESSION['usuario_id']) {
            $_SESSION['error'] = 'No puedes cambiar el estado de tu propia cuenta.';
            $this->redirect('usuarios');
        }

        $usuario = $this->usuarioModel->obtenerPorId($id_usuario);

        if (!$usuario) {
            $_SESSION['error'] = 'El usuario no existe.';
            $this->redirect('usuarios');
        }

        $nuevoEstado = $usuario['estado'] === 'ACTIVO'
            ? 'INACTIVO'
            : 'ACTIVO';

        if ($this->usuarioModel->cambiarEstado($id_usuario, $nuevoEstado)) {
            $_SESSION['exito'] = 'Estado del usuario actualizado.';
        } else {
            $_SESSION['error'] = 'No se pudo cambiar el estado.';
        }

        $this->redirect('usuarios');
    }

    // Eliminar usuario
    public function eliminar()
    {
        requerir_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('usuarios');
        }

        $id_usuario = (int) ($_POST['id_usuario'] ?? 0);

        if ($id_usuario === (int) $_SESSION['usuario_id']) {
            $_SESSION['error'] = 'No puedes eliminar tu propia cuenta.';
            $this->redirect('usuarios');
        }

        $usuario = $this->usuarioModel->obtenerPorId($id_usuario);

        if (!$usuario) {
            $_SESSION['error'] = 'El usuario no existe.';
            $this->redirect('usuarios');
        }

        if ($this->usuarioModel->tieneAlquileresActivos($id_usuario)) {
            $_SESSION['error'] = 'No se puede eliminar: el usuario tiene alquileres activos o con demora.';
            $this->redirect('usuarios');
        }

        try {
            if ($this->usuarioModel->eliminar($id_usuario)) {
                $_SESSION['exito'] = 'Usuario eliminado correctamente.';
            } else {
                $_SESSION['error'] = 'No se pudo eliminar el usuario.';
            }
        } catch (mysqli_sql_exception $e) {
            $_SESSION['error'] = 'No se puede eliminar este usuario porque tiene registros relacionados. Puedes inactivarlo.';
        }

        $this->redirect('usuarios');
    }
}
