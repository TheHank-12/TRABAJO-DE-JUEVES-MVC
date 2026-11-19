
<?php
$alquiler = $alquiler ?? [];
?>

<div class="pagina-header">
    <div>
        <h1>Detalle del alquiler</h1>
        <p>Información del alquiler seleccionado.</p>
    </div>

    <a href="<?= BASE_URL ?>public/alquileres" class="btn btn-secondary">
        Volver al listado
    </a>
</div>

<?php if (!empty($alquiler)): ?>

    <div class="formulario-contenedor">

        <table class="tabla">
            <tbody>
                <tr>
                    <th>ID de alquiler</th>
                    <td>
                        <?= htmlspecialchars($alquiler['id_alquiler'] ?? '') ?>
                    </td>
                </tr>

                <tr>
                    <th>Cliente</th>
                    <td>
                        <?= htmlspecialchars(
                            ($alquiler['usuario_nombre'] ?? '') . ' ' .
                            ($alquiler['usuario_apellido'] ?? '')
                        ) ?>
                    </td>
                </tr>

                <tr>
                    <th>DNI del cliente</th>
                    <td>
                        <?= htmlspecialchars($alquiler['usuario_dni'] ?? '') ?>
                    </td>
                </tr>

                <tr>
                    <th>Libro</th>
                    <td>
                        <?= htmlspecialchars($alquiler['libro_titulo'] ?? '') ?>
                    </td>
                </tr>

                <tr>
                    <th>ISBN</th>
                    <td>
                        <?= htmlspecialchars($alquiler['libro_isbn'] ?? '') ?>
                    </td>
                </tr>

                <tr>
                    <th>Fecha de inicio</th>
                    <td>
                        <?= htmlspecialchars($alquiler['fecha_inicio'] ?? '') ?>
                    </td>
                </tr>

                <tr>
                    <th>Fecha esperada de devolución</th>
                    <td>
                        <?= htmlspecialchars($alquiler['fecha_dev_esperada'] ?? '') ?>
                    </td>
                </tr>

                <tr>
                    <th>Fecha real de devolución</th>
                    <td>
                        <?= htmlspecialchars($alquiler['fecha_dev_real'] ?? 'Pendiente') ?>
                    </td>
                </tr>

                <tr>
                    <th>Monto del alquiler</th>
                    <td>
                        S/ <?= number_format(
                            (float)($alquiler['monto_alquiler'] ?? 0),
                            2
                        ) ?>
                    </td>
                </tr>

                <tr>
                    <th>Método de pago</th>
                    <td>
                        <?= htmlspecialchars($alquiler['metodo_pago'] ?? '') ?>
                    </td>
                </tr>

                <tr>
                    <th>Número de operación</th>
                    <td>
                        <?= htmlspecialchars($alquiler['num_operacion'] ?? 'No registrado') ?>
                    </td>
                </tr>

                <tr>
                    <th>Estado</th>
                    <td>
                        <?= htmlspecialchars($alquiler['estado'] ?? '') ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php if (($alquiler['estado'] ?? '') === 'En Alquiler'): ?>

            <hr>

            <h3>Registrar devolución</h3>

            <form
                action="<?= BASE_URL ?>public/alquileres/devolver"
                method="POST"
                onsubmit="return confirm('¿Confirmas la devolución de este libro?')"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrf_token ?? '') ?>"
                >
                <input
                    type="hidden"
                    name="id_alquiler"
                    value="<?= htmlspecialchars($alquiler['id_alquiler'] ?? '') ?>"
                >

                <div class="form-group">
                    <label for="fecha_dev_real">
                        Fecha de devolución
                    </label>

                    <input
                        type="date"
                        name="fecha_dev_real"
                        id="fecha_dev_real"
                        value="<?= date('Y-m-d') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="cobrar_multa_ahora">
                        ¿Cobrar multa inmediatamente si existe?
                    </label>

                    <select
                        name="cobrar_multa_ahora"
                        id="cobrar_multa_ahora"
                    >
                        <option value="0">No, dejar pendiente</option>
                        <option value="1">Sí, cobrar ahora</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Confirmar devolución
                </button>
            </form>

        <?php endif; ?>

    </div>

<?php else: ?>

    <p>No se encontró el alquiler solicitado.</p>

<?php endif; ?>
