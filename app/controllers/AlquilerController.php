<?php

class AlquilerController extends Controller
{
    public function index(): void
    {
        if (function_exists('requerir_personal')) {
            requerir_personal();
        }

        $estado = isset($_GET['estado']) ? trim((string)$_GET['estado']) : '';$busqueda = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

        $modeloAlquiler = new Alquiler();
        $alquileres =$modeloAlquiler->listarAlquileres($estado,$busqueda);

        $this->view('alquileres/index', [
            'alquileres' => $alquileres,
            'estado'     => $estado,
            'busqueda'   => $busqueda
        ]);
    }

    public function crear(): void
    {
        if (function_exists('requerir_personal')) {
            requerir_personal();
        }

        $modeloAlquiler = new Alquiler();$datosForm = $modeloAlquiler->obtenerDatosFormularioCrear();$idLibroPre = isset($_GET['id_libro']) ? (int)$_GET['id_libro'] : 0;
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {$resultado = $modeloAlquiler->registrarAlquiler($_POST);
            if ($resultado['ok']) {$_SESSION['mensaje'] = "Alquiler registrado exitosamente. Devolución programada para el: " . date('d/m/Y', strtotime($resultado['fecha_dev_esperada']));$_SESSION['tipo_mensaje'] = "success";
                $this->redirect('/alquileres');
            } else {
                $error =$resultado['error'];
            }
        }

        $this->view('alquileres/crear', [
            'clientes'     => $datosForm['clientes'],
            'libros'       => $datosForm['libros'],
            'id_libro_pre' => $idLibroPre,
            'error'        => $error
        ]);
    }

    public function devolver(): void
    {
        if (function_exists('requerir_personal')) {
            requerir_personal();
        }

        $idAlquiler = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $modeloAlquiler = new Alquiler();$datos = $modeloAlquiler->obtenerAlquilerParaDevolucion($idAlquiler);

        if ($datos === null) {$_SESSION['mensaje'] = "Alquiler no encontrado.";
            $_SESSION['tipo_mensaje'] = "danger";
            $this->redirect('/alquileres');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fechaDevReal = trim((string)($_POST['fecha_dev_real'] ?? date('Y-m-d')));
            $cobrarMultaAhora = isset($_POST['cobrar_multa_ahora']);

            $resultado = $modeloAlquiler->procesarDevolucion($idAlquiler, $fechaDevReal,$cobrarMultaAhora);
            if ($resultado['ok']) {$_SESSION['mensaje'] = "Devolución procesada correctamente. Ejemplar devuelto al inventario.";
                $_SESSION['tipo_mensaje'] = "success";
                $this->redirect('/alquileres');
            } else {
                $error =$resultado['error'];
            }
        }

        $this->view('alquileres/devolver', [
            'alquiler'        => $datos['alquiler'],
            'fecha_hoy'       => $datos['fecha_hoy'],
            'dias_demora'     => $datos['dias_demora'],
            'monto_diario_mora'=> $datos['monto_diario_mora'],
            'total_multa'     => $datos['total_multa'],
            'error'           => $error
        ]);
    }

    public function multas(): void
    {
        if (function_exists('requerir_personal')) {
            requerir_personal();
        }

        $modeloAlquiler = new Alquiler();

        if (isset($_GET['cobrar_id'])) {$idMultaCobrar = (int)$_GET['cobrar_id'];$resultado = $modeloAlquiler->cobrarMulta($idMultaCobrar);
            if ($resultado['ok']) {$_SESSION['mensaje'] = "Multa cobrada exitosamente. Estado de cuenta actualizado.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = $resultado['error'];$_SESSION['tipo_mensaje'] = "danger";
            }
            $this->redirect('/alquileres/multas');
        }

        $multas =$modeloAlquiler->listarMultas();

        $this->view('alquileres/multas', [
            'multas' => $multas
        ]);
    }
}
