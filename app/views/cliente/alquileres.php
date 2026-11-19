
<?php
$alquileres = $alquileres ?? [];
?>

<div class="pagina-header">
    <div>
        <h1>Mis alquileres</h1>
        <p>Consulta los libros que has alquilado y su estado.</p>
    </div>

    <a href="<?= BASE_URL ?>catalogo" class="btn btn-secondary">
        Volver al catálogo
    </a>
</div>

<div class="formulario-contenedor">

    <?php if (!empty($alquileres)): ?>

        <div class="tabla-responsive">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Libro</th>
                        <th>ISBN</th>
                        <th>Fecha de inicio</th>
                        <th>Devolución esperada</th>
                        <th>Devolución real</th>
                        <th>Monto alquiler</th>
                        <th>Multa</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($alquileres as $alquiler): ?>

                        <tr>
                            <td>
                                <strong>
                                    <?= htmlspecialchars($alquiler['libro_titulo'] ?? '') ?>
                                </strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($alquiler['libro_isbn'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($alquiler['fecha_inicio'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($alquiler['fecha_dev_esperada'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($alquiler['fecha_dev_real'] ?? 'Pendiente') ?>
                            </td>

                            <td>
                                S/ <?= number_format(
                                    (float)($alquiler['monto_alquiler'] ?? 0),
                                    2
                                ) ?>
                            </td>

                            <td>
                                <?php if (!empty($alquiler['monto_multa'])): ?>

                                    S/ <?= number_format(
                                        (float)$alquiler['monto_multa'],
                                        2
                                    ) ?>

                                    <br>

                                    <?php if (($alquiler['estado_multa'] ?? '') === 'Pendiente'): ?>
                                        <span class="badge badge-warning">
                                            Pendiente de pago
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success">
                                            Pagada
                                        </span>
                                    <?php endif; ?>

                                <?php else: ?>

                                    Sin multa

                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (($alquiler['estado'] ?? '') === 'En Alquiler'): ?>

                                    <span class="badge badge-warning">
                                        En alquiler
                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-success">
                                        Devuelto
                                    </span>

                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <p>Aún no tienes alquileres registrados.</p>

        <a href="<?= BASE_URL ?>catalogo" class="btn btn-primary">
            Explorar catálogo
        </a>

    <?php endif; ?>

</div>
