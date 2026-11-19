```php
<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Mis Compras</h2>
            <p class="text-muted mb-0">
                Consulta el historial de tus compras realizadas.
            </p>
        </div>

        <a href="<?= BASE_URL ?>public/catalogo" class="btn btn-outline-primary">
            <i class="fas fa-book"></i> Ir al catálogo
        </a>
    </div>

    <?php if (!empty($compras)): ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>N.º comprobante</th>
                                <th>Fecha</th>
                                <th>Método de pago</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($compras as $compra): ?>

                                <tr>
                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(
                                                $compra['num_comprobante'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= !empty($compra['fecha_venta'])
                                            ? date('d/m/Y H:i', strtotime($compra['fecha_venta']))
                                            : '—' ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $compra['metodo_pago'] ?? '—',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td class="text-end fw-bold">
                                        S/ <?= number_format(
                                            (float) ($compra['total'] ?? 0),
                                            2
                                        ) ?>
                                    </td>

                                    <td class="text-center">
                                        <a
                                            href="<?= BASE_URL ?>public/ventas/comprobante?id=<?= (int) $compra['id_venta'] ?>"
                                            class="btn btn-sm btn-primary"
                                        >
                                            <i class="fas fa-eye"></i>
                                            Ver comprobante
                                        </a>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    <?php else: ?>

        <div class="card shadow-sm text-center py-5">
            <div class="card-body">

                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>

                <h4 class="fw-bold">Aún no tienes compras</h4>

                <p class="text-muted">
                    Cuando realices una compra, aparecerá aquí.
                </p>

                <a href="<?= BASE_URL ?>public/catalogo" class="btn btn-primary">
                    Explorar catálogo
                </a>

            </div>
        </div>

    <?php endif; ?>

</div>
```