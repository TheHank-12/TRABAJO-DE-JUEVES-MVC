
<?php
$direccion = !empty($venta['direccion'])
    ? $venta['direccion']
    : 'Lima, Perú';
?>

<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">

        <a href="<?= BASE_URL ?>public/ventas" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al historial
        </a>

        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Imprimir comprobante
        </button>

    </div>

    <div class="card shadow-sm" id="comprobante">

        <div class="card-body p-4 p-md-5">

            <!-- Encabezado -->
            <div class="text-center mb-4">

                <h2 class="fw-bold mb-1">
                    LIBRERÍA
                </h2>

                <p class="text-muted mb-1">
                    Sistema de venta y alquiler de libros
                </p>

                <h4 class="mt-3 fw-bold">
                    COMPROBANTE DE VENTA
                </h4>

                <h5 class="text-primary">
                    <?= htmlspecialchars(
                        $venta['num_comprobante'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </h5>

            </div>

            <hr>

            <!-- Datos de la venta -->
            <div class="row mb-4">

                <div class="col-md-6 mb-3">

                    <h5 class="fw-bold">Datos del cliente</h5>

                    <p class="mb-1">
                        <strong>Cliente:</strong>
                        <?= htmlspecialchars(
                            ($venta['nombre'] ?? '') . ' ' . ($venta['apellido'] ?? ''),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <p class="mb-1">
                        <strong>DNI:</strong>
                        <?= htmlspecialchars(
                            $venta['dni'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <p class="mb-1">
                        <strong>Email:</strong>
                        <?= htmlspecialchars(
                            $venta['email'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <p class="mb-1">
                        <strong>Dirección:</strong>
                        <?= htmlspecialchars(
                            $direccion,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <p class="mb-1">
                        <strong>Teléfono:</strong>
                        <?= htmlspecialchars(
                            $venta['telefono'] ?? 'No registrado',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                </div>

                <div class="col-md-6 mb-3">

                    <h5 class="fw-bold">Datos de la venta</h5>

                    <p class="mb-1">
                        <strong>N.º comprobante:</strong>
                        <?= htmlspecialchars(
                            $venta['num_comprobante'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <p class="mb-1">
                        <strong>Fecha:</strong>
                        <?= !empty($venta['fecha_venta'])
                            ? date('d/m/Y H:i', strtotime($venta['fecha_venta']))
                            : '—' ?>
                    </p>

                    <p class="mb-1">
                        <strong>Método de pago:</strong>
                        <?= htmlspecialchars(
                            $venta['metodo_pago'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <?php if (!empty($venta['num_operacion'])): ?>

                        <p class="mb-1">
                            <strong>N.º operación:</strong>
                            <?= htmlspecialchars(
                                $venta['num_operacion'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                    <?php endif; ?>

                </div>

            </div>

            <hr>

            <!-- Detalle de productos -->
            <h5 class="fw-bold mb-3">Detalle de productos</h5>

            <div class="table-responsive">

                <table class="table table-bordered align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>Libro</th>
                            <th>ISBN</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio unitario</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($detalles)): ?>

                            <?php foreach ($detalles as $detalle): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars(
                                            $detalle['titulo'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $detalle['isbn'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td class="text-center">
                                        <?= (int) $detalle['cantidad'] ?>
                                    </td>

                                    <td class="text-end">
                                        S/ <?= number_format(
                                            (float) $detalle['precio_unitario'],
                                            2
                                        ) ?>
                                    </td>

                                    <td class="text-end">
                                        S/ <?= number_format(
                                            (float) $detalle['subtotal'],
                                            2
                                        ) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="text-center">
                                    No hay productos registrados.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- Totales -->
            <div class="row justify-content-end mt-4">

                <div class="col-md-5">

                    <table class="table">

                        <tr>
                            <th>Subtotal:</th>
                            <td class="text-end">
                                S/ <?= number_format((float) $venta['subtotal'], 2) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>IGV:</th>
                            <td class="text-end">
                                S/ <?= number_format((float) $venta['igv'], 2) ?>
                            </td>
                        </tr>

                        <tr class="table-light">
                            <th class="fs-5">TOTAL:</th>
                            <td class="text-end fw-bold fs-5">
                                S/ <?= number_format((float) $venta['total'], 2) ?>
                            </td>
                        </tr>

                    </table>

                </div>

            </div>

            <hr>

            <!-- Pie -->
            <div class="text-center mt-4">

                <h6 class="fw-bold">
                    ¡Gracias por su compra!
                </h6>

                <p class="text-muted mb-0">
                    Conserve este comprobante como constancia de su compra.
                </p>

            </div>

        </div>

    </div>

</div>

<style>
@media print {

    body {
        background: white !important;
    }

    .no-print,
    header,
    footer,
    nav {
        display: none !important;
    }

    #comprobante {
        border: none !important;
        box-shadow: none !important;
    }

    #comprobante .card-body {
        padding: 0 !important;
    }

    .container {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    @page {
        size: A4;
        margin: 15mm;
    }
}
</style>
