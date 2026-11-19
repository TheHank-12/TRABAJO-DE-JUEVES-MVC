
<?php
$multas = $multas ?? [];
?>

<div class="pagina-header">
    <div>
        <h1>Gestión de multas</h1>
        <p>Consulta y administra las multas generadas por devoluciones tardías.</p>
    </div>

    <a href="<?= BASE_URL ?>public/alquileres" class="btn btn-secondary">
        Volver a alquileres
    </a>
</div>

<div class="formulario-contenedor">

    <?php if (!empty($multas)): ?>

        <div class="tabla-responsive">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID multa</th>
                        <th>Alquiler</th>
                        <th>Cliente</th>
                        <th>Libro</th>
                        <th>Días de atraso</th>
                        <th>Monto diario</th>
                        <th>Monto total</th>
                        <th>Estado</th>
                        <th>Fecha de pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($multas as $multa): ?>

                        <tr>
                            <td>
                                <?= htmlspecialchars($multa['id_multa'] ?? '') ?>
                            </td>

                            <td>
                                #<?= htmlspecialchars($multa['id_alquiler'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    ($multa['usuario_nombre'] ?? '') . ' ' .
                                    ($multa['usuario_apellido'] ?? '')
                                ) ?>
                                <br>
                                <small>
                                    DNI: <?= htmlspecialchars($multa['usuario_dni'] ?? '') ?>
                                </small>
                            </td>

                            <td>
                                <?= htmlspecialchars($multa['libro_titulo'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($multa['dias_atraso'] ?? '0') ?>
                            </td>

                            <td>
                                S/ <?= number_format(
                                    (float)($multa['monto_diario'] ?? 0),
                                    2
                                ) ?>
                            </td>

                            <td>
                                <strong>
                                    S/ <?= number_format(
                                        (float)($multa['monto_total'] ?? 0),
                                        2
                                    ) ?>
                                </strong>
                            </td>

                            <td>
                                <?php if (($multa['estado'] ?? '') === 'Pagada'): ?>
                                    <span class="badge badge-success">Pagada</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pendiente</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= !empty($multa['fecha_pago'])
                                    ? htmlspecialchars($multa['fecha_pago'])
                                    : 'Sin pagar' ?>
                            </td>

                            <td>
                                <?php if (($multa['estado'] ?? '') === 'Pendiente'): ?>

                                    <form
                                    action="<?= BASE_URL ?>public/alquileres/cobrar-multa"
                                    method="POST"
                                    onsubmit="return confirm('¿Confirmas el cobro de esta multa?')"
                                >
                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars($csrf_token ?? '') ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="id_multa"
                                        value="<?= htmlspecialchars($multa['id_multa'] ?? '') ?>"
                                    >

                                        <button
                                            type="submit"
                                            class="btn btn-primary btn-sm"
                                        >
                                            Registrar pago
                                        </button>
                                    </form>

                                <?php else: ?>

                                    <span>Sin acciones</span>

                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <p>No hay multas registradas actualmente.</p>

    <?php endif; ?>

</div>
