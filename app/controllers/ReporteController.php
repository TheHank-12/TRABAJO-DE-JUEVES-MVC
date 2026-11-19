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
            'multas'        => $multas
        ]);
    }

    public function cobrarMulta() {
        $id_multa = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id_multa > 0) {
            $resultado = $this->reporteModel->cobrarMulta($id_multa);
            $_SESSION['mensaje'] = $resultado['mensaje'];
            $_SESSION['tipo_mensaje'] = $resultado['exito'] ? 'success' : 'danger';
        }
        $this->redirect('public/reportes/multas');
    }
}