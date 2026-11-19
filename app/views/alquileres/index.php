
<?php
$alquileres = $alquileres ?? [];
$fecha_desde = $fecha_desde ?? '';
$fecha_hasta = $fecha_hasta ?? '';
$busqueda = $busqueda ?? '';
$csrf_token = $csrf_token ?? '';
?>

<div class="pagina-header">
    <div>
        <h1>Gestión de alquileres</h1>
        <p>Consulta y administra los alquileres registrados.</p>
    </div>

    <a href="<?= BASE_URL ?>public/alquileres/crear" class="btn btn-primary">
        + Registrar alquiler
    </a>
</div>

<form method="GET" action="<?= BASE_URL ?>public/alquileres" class="form-filtros">

    <div class="form-group">
        <label for="busqueda">Buscar</label>
        <input
            type="text"
            id="busqueda"
            name="busqueda"
            value="<?= htmlspecialchars($busqueda) ?>"
            placeholder="DNI, cliente o libro..."
        >
    </div>

    <div class="form-group">
        <label for="fecha_desde">Desde</label>
        <input
            type="date"
            id="fecha_desde"
            name="fecha_desde"
            value="<?= htmlspecialchars($fecha_desde) ?>"
        >
    </div>

    <div class="form-group">
        <label for="fecha_hasta">Hasta</label>
        <input
            type="date"
            id="fecha_hasta"
            name="fecha_hasta"
            value="<?= htmlspecialchars($fecha_hasta) ?>"
        >
    </div>

    <button type="submit" class="btn btn-primary">Filtrar</button>

    <a href="<?= BASE_URL ?>public/alquileres" class="btn btn-secondary">
        Limpiar
    </a>
</form>

<div class="tabla-contenedor">
    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Libro</th>
                <th>Fecha de inicio</th>
                <th>Devolución esperada</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($alquileres)): ?>

                <?php foreach ($alquileres as $alquiler): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($alquiler['id_alquiler'] ?? '') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                ($alquiler['usuario_nombre'] ?? '') . ' ' .
                                ($alquiler['usuario_apellido'] ?? '')
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($alquiler['libro_titulo'] ?? '') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($alquiler['fecha_inicio'] ?? '') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($alquiler['fecha_dev_esperada'] ?? '') ?>
                        </td>

                        <td>
                            S/ <?= number_format((float)($alquiler['monto_alquiler'] ?? 0), 2) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($alquiler['estado'] ?? '') ?>
                        </td>

                        <td>
                            <a
                                href="<?= BASE_URL ?>public/alquileres/detalle?id=<?= urlencode($alquiler['id_alquiler']) ?>"
                                class="btn btn-secondary"
                            >
                                Ver
                            </a>

                            <?php if (($alquiler['estado'] ?? '') === 'En Alquiler'): ?>
                               <form
                                    method="POST"
                                    action="<?= BASE_URL ?>public/alquileres/devolver"
                                    style="display:inline"
                                    onsubmit="return confirm('¿Confirmas la devolución de este libro?')"
                                >
                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars($csrf_token) ?>"
                                    >
                                    <input
                                        type="hidden"
                                        name="id_alquiler"
                                        value="<?= htmlspecialchars($alquiler['id_alquiler']) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="fecha_dev_real"
                                        value="<?= date('Y-m-d') ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="cobrar_multa_ahora"
                                        value="0"
                                    >

                                    <button type="submit" class="btn btn-success">
                                        Devolver
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td colspan="8" style="text-align:center;">
                        No hay alquileres registrados.
                    </td>
                </tr>

            <?php endif; ?>
        </tbody>
    </table>
</div>
