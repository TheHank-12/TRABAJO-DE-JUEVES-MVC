<?php

class ReporteController extends Controller {
    private $reporteModel;

    public function __construct() {
        requerir_personal();
        $this->reporteModel = new Reporte();
    }

    public function index() {
        $resumen          = $this->reporteModel->obtenerResumenGeneral();
        $ultimas_ventas   = $this->reporteModel->obtenerVentas(5);
        $libros_bajo_stk  = $this->reporteModel->obtenerLibrosBajoStock();

        $this->view('reportes/index', [
            'titulo_pagina'   => 'Reportes y Estadísticas',
            'resumen'         => $resumen,
            'ultimas_ventas'  => $ultimas_ventas,
            'libros_bajo_stk' => $libros_bajo_stk
        ]);
    }

    public function imprimir() {
        $ventas     = $this->reporteModel->obtenerVentas();
        $alquileres = $this->reporteModel->obtenerAlquileres();

        $this->view('reportes/imprimir', [
            'titulo_pagina'    => 'Reporte Ejecutivo Consolidado',
            'fecha_generacion' => date('d/m/Y H:i:s'),
            'ventas'           => $ventas,
            'alquileres'       => $alquileres
        ]);
    }

    public function multas() {
        $multas = $this->reporteModel->obtenerMultas();

        $this->view('reportes/multas', [
            'titulo_pagina' => 'Control de Multas por Morosidad',
            'multas'        => $multas,
            'csrf_token'    => generar_token_csrf()
        ]);
    }

    public function cobrarMulta() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/reportes/multas');
            return;
        }

        $token = $_POST['csrf_token'] ?? '';

        if (!validar_token_csrf($token)) {
            $_SESSION['mensaje'] = 'Solicitud no válida. Actualice la página e intente nuevamente.';
            $_SESSION['tipo_mensaje'] = 'danger';

            $this->redirect('public/reportes/multas');
            return;
        }

        $id_multa = filter_input(INPUT_POST, 'id_multa', FILTER_VALIDATE_INT);

        if (!$id_multa || $id_multa <= 0) {
            $_SESSION['mensaje'] = 'Identificador de multa no válido.';
            $_SESSION['tipo_mensaje'] = 'danger';

            $this->redirect('public/reportes/multas');
            return;
        }

        $resultado = $this->reporteModel->cobrarMulta($id_multa);

        $_SESSION['mensaje'] = $resultado['mensaje'];
        $_SESSION['tipo_mensaje'] = $resultado['exito'] ? 'success' : 'danger';

        $this->redirect('public/reportes/multas');
    }
}