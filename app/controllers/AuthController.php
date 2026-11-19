<?php

class AuthController extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    private function tokenCsrf()
    {
        if (empty($_SESSION['csrf_auth'])) {
            $_SESSION['csrf_auth'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_auth'];
    }

    private function csrfValido()
    {
        return isset($_POST['csrf_token'], $_SESSION['csrf_auth'])
            && hash_equals($_SESSION['csrf_auth'], (string) $_POST['csrf_token']);
    }

    public function login()
    {
        if (esta_autenticado()) {
            $this->redirect('public/');
        }

        $error = '';
        $email = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$this->csrfValido()) {
                $error = 'La solicitud expiró. Actualice la página e inténtelo nuevamente.';
            } elseif ($email === '' || $password === '') {
                $error = 'Por favor, complete todos los campos.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Ingrese un correo electrónico válido.';
            } else {
                $usuario = $this->usuarioModel->buscarPorEmail($email);
                if (!$usuario) {
                    $error = 'El correo electrónico no se encuentra registrado.';
                } elseif ($usuario['estado'] !== 'ACTIVO') {
                    $error = 'Su cuenta se encuentra INACTIVA. Póngase en contacto con el administrador.';
                } elseif (!password_verify($password, $usuario['password'])) {
                    $error = 'Contraseña incorrecta. Verifique sus credenciales.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['usuario_id'] = (int) $usuario['id_usuario'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    $_SESSION['usuario_apellido'] = $usuario['apellido'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    $_SESSION['usuario_dni'] = $usuario['dni'];
                    $_SESSION['rol_nombre'] = $usuario['rol_nombre'];
                    unset($_SESSION['csrf_auth']);
                    $_SESSION['mensaje'] = 'Bienvenido(a) ' . $usuario['nombre'] . ' al sistema.';
                    $_SESSION['tipo_mensaje'] = 'success';
                    $this->redirect('public/');
                }
            }
        }

        $this->view('auth/login', [
            'titulo_pagina' => 'Iniciar Sesión', 'error' => $error,
            'email' => $email, 'csrf_token' => $this->tokenCsrf()
        ]);
    }

    public function registro()
    {
        if (esta_autenticado()) {
            $this->redirect('public/');
        }

        $error = '';
        $datos = ['dni'=>'', 'nombre'=>'', 'apellido'=>'', 'email'=>'', 'telefono'=>'', 'direccion'=>''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($datos as $campo => $valor) {
                $datos[$campo] = trim((string) ($_POST[$campo] ?? ''));
            }
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (!$this->csrfValido()) {
                $error = 'La solicitud expiró. Actualice la página e inténtelo nuevamente.';
            } elseif ($datos['dni'] === '' || $datos['nombre'] === '' || $datos['apellido'] === '' || $datos['email'] === '' || $password === '') {
                $error = 'Por favor, complete todos los campos obligatorios.';
            } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'Ingrese un correo electrónico válido.';
            } elseif ($password !== $confirm) {
                $error = 'Las contraseñas ingresadas no coinciden.';
            } elseif (strlen($password) < 6) {
                $error = 'La contraseña debe contener al menos 6 caracteres.';
            } elseif ($this->usuarioModel->existeDni($datos['dni'])) {
                $error = 'El número de documento ingresado ya se encuentra registrado.';
            } elseif ($this->usuarioModel->existeEmail($datos['email'])) {
                $error = 'El correo electrónico ingresado ya está registrado.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                if ($this->usuarioModel->registrarCliente($datos, $hash)) {
                    unset($_SESSION['csrf_auth']);
                    $_SESSION['mensaje'] = 'Cuenta creada con éxito. Ahora puede iniciar sesión.';
                    $_SESSION['tipo_mensaje'] = 'success';
                    $this->redirect('public/login');
                }
                $error = 'No fue posible crear la cuenta. Verifique que el rol Cliente exista.';
            }
        }

        $this->view('auth/registro', [
            'titulo_pagina' => 'Registro de Cliente', 'error' => $error,
            'datos' => $datos, 'csrf_token' => $this->tokenCsrf()
        ]);
    }

    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo 'Método HTTP no permitido.';
            return;
        }
        if (!$this->csrfValido()) {
            http_response_code(403);
            echo 'Solicitud no válida.';
            return;
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        session_start();
        $_SESSION['mensaje'] = 'Ha cerrado su sesión de manera segura.';
        $_SESSION['tipo_mensaje'] = 'info';
        $this->redirect('public/login');
    }
}
