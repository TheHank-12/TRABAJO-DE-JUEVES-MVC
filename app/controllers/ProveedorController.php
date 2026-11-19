<?php

class ProveedorController extends Controller {
    private $proveedorModel;

    public function __construct() {
        requerir_admin();
        $this->proveedorModel = new Proveedor();
    }

    public function index() {
        $proveedores = $this->proveedorModel->listarTodos();
        $this->view('proveedores/index', [
            'titulo_pagina' => 'Gestión de Proveedores',
            'proveedores'   => $proveedores
        ]);
    }

    public function crear() {
        $this->view('proveedores/crear', [
            'titulo_pagina' => 'Registrar Proveedor',
            'error'         => '',
            'datos'         => [
                'ruc_dni'      => '',
                'razon_social' => '',
                'contacto'     => '',
                'telefono'     => '',
                'email'        => '',
                'direccion'    => ''
            ]
        ]);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/proveedores');
        }

        $datos = [
            'ruc_dni'      => trim($_POST['ruc_dni'] ?? ''),
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'contacto'     => trim($_POST['contacto'] ?? ''),
            'telefono'     => trim($_POST['telefono'] ?? ''),
            'email'        => trim($_POST['email'] ?? ''),
            'direccion'    => trim($_POST['direccion'] ?? '')
        ];

        if (empty($datos['ruc_dni']) || empty($datos['razon_social'])) {
            $this->view('proveedores/crear', [
                'titulo_pagina' => 'Registrar Proveedor',
                'error'         => 'El RUC/DNI y la Razón Social son campos obligatorios.',
                'datos'         => $datos
            ]);
            return;
        }

        if ($this->proveedorModel->existeRucDni($datos['ruc_dni'])) {
            $this->view('proveedores/crear', [
                'titulo_pagina' => 'Registrar Proveedor',
                'error'         => 'El número de RUC/DNI ya se encuentra registrado.',
                'datos'         => $datos
            ]);
            return;
        }

        if ($this->proveedorModel->crear($datos)) {
            $_SESSION['mensaje'] = 'Proveedor registrado con éxito.';
            $_SESSION['tipo_mensaje'] = 'success';
            $this->redirect('public/proveedores');
        } else {
            $this->view('proveedores/crear', [
                'titulo_pagina' => 'Registrar Proveedor',
                'error'         => 'Error al registrar el proveedor en la base de datos.',
                'datos'         => $datos
            ]);
        }
    }

    public function editar() {
        $id_proveedor = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $prov = $this->proveedorModel->obtenerPorId($id_proveedor);

        if (!$prov) {
            $_SESSION['mensaje'] = 'Proveedor no encontrado.';
            $_SESSION['tipo_mensaje'] = 'danger';
            $this->redirect('public/proveedores');
        }

        $this->view('proveedores/editar', [
            'titulo_pagina' => 'Editar Proveedor',
            'prov'          => $prov,
            'error'         => ''
        ]);
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/proveedores');
        }

        $id_proveedor = isset($_POST['id_proveedor']) ? intval($_POST['id_proveedor']) : 0;
        $provActual = $this->proveedorModel->obtenerPorId($id_proveedor);

        if (!$provActual) {
            $_SESSION['mensaje'] = 'Proveedor no encontrado.';
            $_SESSION['tipo_mensaje'] = 'danger';
            $this->redirect('public/proveedores');
        }

        $datos = [
            'id_proveedor' => $id_proveedor,
            'ruc_dni'      => trim($_POST['ruc_dni'] ?? ''),
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'contacto'     => trim($_POST['contacto'] ?? ''),
            'telefono'     => trim($_POST['telefono'] ?? ''),
            'email'        => trim($_POST['email'] ?? ''),
            'direccion'    => trim($_POST['direccion'] ?? '')
        ];

        if (empty($datos['ruc_dni']) || empty($datos['razon_social'])) {
            $this->view('proveedores/editar', [
                'titulo_pagina' => 'Editar Proveedor',
                'prov'          => $datos,
                'error'         => 'El RUC/DNI y la Razón Social son campos obligatorios.'
            ]);
            return;
        }

        if ($this->proveedorModel->existeRucDni($datos['ruc_dni'], $id_proveedor)) {
            $this->view('proveedores/editar', [
                'titulo_pagina' => 'Editar Proveedor',
                'prov'          => $datos,
                'error'         => 'El RUC/DNI ingresado ya está en uso por otro proveedor.'
            ]);
            return;
        }

        if ($this->proveedorModel->actualizar($id_proveedor, $datos)) {
            $_SESSION['mensaje'] = 'Proveedor actualizado correctamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            $this->redirect('public/proveedores');
        } else {
            $this->view('proveedores/editar', [
                'titulo_pagina' => 'Editar Proveedor',
                'prov'          => $datos,
                'error'         => 'Error al actualizar el proveedor.'
            ]);
        }
    }

    public function eliminar() {
        $id_proveedor = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $total_libros = $this->proveedorModel->contarLibrosAsociados($id_proveedor);

        if ($total_libros > 0) {
            $_SESSION['mensaje'] = "No se puede eliminar el proveedor porque suministra $total_libros libro(s) registrado(s) en el sistema.";
            $_SESSION['tipo_mensaje'] = 'warning';
        } else {
            if ($this->proveedorModel->eliminar($id_proveedor)) {
                $_SESSION['mensaje'] = 'Proveedor eliminado correctamente.';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al eliminar el proveedor.';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        }

        $this->redirect('public/proveedores');
    }
}