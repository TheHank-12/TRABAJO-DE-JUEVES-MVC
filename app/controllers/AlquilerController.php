<?php

class AlquilerController extends Controller
{
    public function index(): void
    {
        if (function_exists('requerir_autenticacion')) {
            requerir_autenticacion();
        }

        $esPersonal = function_exists('es_personal') ? es_personal() : false;
        $usuarioId  = (int)($_SESSION['usuario_id'] ?? 0);
        $idFiltro   = $esPersonal ? null : $usuarioId;

        $estado   = isset($_GET['estado']) ? trim((string)$_GET['estado']) : '';
        $busqueda = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

        $modeloAlquiler = new Alquiler();
        $alquileres = $modeloAlquiler->listarAlquileres($estado, $busqueda, $idFiltro);

        $this->view('alquileres/index', [
            'alquileres' => $alquileres,
            'estado'     => $estado,
            'busqueda'   => $busqueda
        ]);
    }
}
