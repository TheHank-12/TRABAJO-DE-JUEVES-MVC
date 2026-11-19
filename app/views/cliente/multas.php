```php
<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Mis Multas</h2>
            <p class="text-muted mb-0">
                Consulta las multas asociadas a tus alquileres.
            </p>
        </div>

        <a href="<?= BASE_URL ?>public/mis-alquileres"
           class="btn btn-outline-primary">
            <i class="fas fa-book"></i> Mis alquileres
        </a>
    </div>

    <?php if (!empty($multas)): ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>Libro</th>
                                <th>ID alquiler</th>
                                <th>Monto de multa</th>
                                <th>Estado</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($multas as $multa): ?>

                                <?php
                                $estado = strtoupper(trim($multa['estado_multa'] ?? 'PENDIENTE'));
                                $pagada = in_array($estado, ['PAGADA', 'PAGADO'], true);
                                ?>

                                <tr>
                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(
                                                $multa['libro_titulo'] ?? 'Libro no disponible',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted">
                                            ISBN:
                                            <?= htmlspecialchars(
                                                $multa['libro_isbn'] ?? '—',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </small>
                                    </td>

                                    <td>
                                        #<?= (int) $multa['id_alquiler'] ?>
                                    </td>

                                    <td class="fw-bold">
                                        S/ <?= number_format(
                                            (float) $multa['monto_multa'],
                                            2
                                        ) ?>
                                    </td>

                                    <td>
                                        <?php if ($pagada): ?>
                                            <span class="badge bg-success">
                                                Pagada
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                <?= htmlspecialchars(
                                                    ucfirst(strtolower($estado)),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </span>
                                        <?php endif; ?>
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

                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                <h4 class="fw-bold">No tienes multas registradas</h4>

                <p class="text-muted">
                    Actualmente no tienes multas asociadas a tus alquileres.
                </p>

                <a href="<?= BASE_URL ?>public/catalogo"
                   class="btn btn-primary">
                    Explorar catálogo
                </a>

            </div>
        </div>

    <?php endif; ?>

</div>
```