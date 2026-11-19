
<style>
.ventas-page {
    padding: 25px 0 40px;
    color: #263445;
}

.ventas-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.ventas-header h2 {
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 5px;
    color: #24364b;
}

.ventas-header p {
    color: #718096;
    margin: 0;
}

.ventas-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 10px 17px;
    border-radius: 7px;
    text-decoration: none;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: .2s;
}

.ventas-btn-primary {
    background: #2457a7;
    color: white;
}

.ventas-btn-primary:hover {
    background: #19447f;
    color: white;
}

.ventas-btn-search {
    background: #34495e;
    color: white;
}

.ventas-btn-search:hover {
    background: #243447;
}

.ventas-btn-info {
    background: #e5f1ff;
    color: #2457a7;
    padding: 7px 12px;
    font-size: 13px;
}

.ventas-btn-info:hover {
    background: #2457a7;
    color: white;
}

.ventas-card {
    background: #fff;
    border: 1px solid #e5eaf0;
    border-radius: 11px;
    box-shadow: 0 3px 12px rgba(30, 50, 80, .05);
    margin-bottom: 22px;
    overflow: hidden;
}

.ventas-card-body {
    padding: 22px;
}

.ventas-search {
    display: flex;
    gap: 10px;
}

.ventas-search input {
    flex: 1;
    min-width: 0;
    padding: 11px 14px;
    border: 1px solid #d6dee8;
    border-radius: 7px;
    outline: none;
    font-size: 14px;
}

.ventas-search input:focus {
    border-color: #3978c7;
    box-shadow: 0 0 0 3px rgba(57, 120, 199, .12);
}

.ventas-table-wrapper {
    overflow-x: auto;
}

.ventas-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
    font-size: 13px;
}

.ventas-table thead {
    background: #263b55;
    color: white;
}

.ventas-table th {
    padding: 15px 13px;
    text-align: left;
    font-weight: 600;
}

.ventas-table td {
    padding: 14px 13px;
    border-bottom: 1px solid #edf0f4;
    vertical-align: middle;
}

.ventas-table tbody tr:nth-child(even) {
    background: #f8fafc;
}

.ventas-table tbody tr:hover {
    background: #edf5ff;
}

.ventas-comprobante {
    color: #2457a7;
    font-weight: 700;
}

.ventas-cliente {
    font-weight: 600;
    color: #34465c;
}

.ventas-metodo {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 20px;
    background: #eaf1fa;
    color: #31577f;
    font-size: 11px;
    font-weight: 700;
}

.ventas-total {
    font-weight: 700;
    color: #1d744d;
}

.ventas-empty {
    text-align: center;
    padding: 45px 15px !important;
    color: #718096;
}

.ventas-empty-icon {
    font-size: 35px;
    margin-bottom: 12px;
    color: #aab8c8;
}

.ventas-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    padding: 15px 22px;
    border-top: 1px solid #edf0f4;
    color: #718096;
    font-size: 13px;
}

@media (max-width: 650px) {
    .ventas-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .ventas-search {
        flex-direction: column;
    }

    .ventas-card-body {
        padding: 15px;
    }
}
</style>

<div class="container ventas-page">

    <!-- Encabezado -->
    <div class="ventas-header">

        <div>
            <h2>Historial de Ventas</h2>
            <p>Consulte las ventas registradas y sus comprobantes.</p>
        </div>

        <a href="<?= BASE_URL ?>public/ventas/crear"
           class="ventas-btn ventas-btn-primary">
            <span>＋</span> Nueva venta
        </a>

    </div>

    <!-- Buscador -->
    <div class="ventas-card">

        <div class="ventas-card-body">

            <form method="GET"
                  action="<?= BASE_URL ?>public/ventas"
                  class="ventas-search">

                <input
                    type="text"
                    name="q"
                    placeholder="Buscar por comprobante, DNI o nombre del cliente..."
                    value="<?= htmlspecialchars($busqueda ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >

                <button type="submit"
                        class="ventas-btn ventas-btn-search">
                    Buscar
                </button>

                <?php if (!empty($busqueda)): ?>
                    <a href="<?= BASE_URL ?>public/ventas"
                       class="ventas-btn"
                       style="background:#edf0f4;color:#34465c;">
                        Limpiar
                    </a>
                <?php endif; ?>

            </form>

        </div>

    </div>

    <!-- Tabla -->
    <div class="ventas-card">

        <div class="ventas-table-wrapper">

            <table class="ventas-table">

                <thead>
                    <tr>
                        <th>Comprobante</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>DNI</th>
                        <th>Método de pago</th>
                        <th>N.º operación</th>
                        <th>Subtotal</th>
                        <th>IGV</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($ventas)): ?>

                        <?php foreach ($ventas as $venta): ?>

                            <tr>

                                <td class="ventas-comprobante">
                                    <?= htmlspecialchars(
                                        $venta['num_comprobante'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= !empty($venta['fecha_venta'])
                                        ? date('d/m/Y H:i', strtotime($venta['fecha_venta']))
                                        : '—' ?>
                                </td>

                                <td class="ventas-cliente">
                                    <?= htmlspecialchars(
                                        ($venta['nombre'] ?? '') . ' ' . ($venta['apellido'] ?? ''),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $venta['dni'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <span class="ventas-metodo">
                                        <?= htmlspecialchars(
                                            $venta['metodo_pago'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= !empty($venta['num_operacion'])
                                        ? htmlspecialchars($venta['num_operacion'], ENT_QUOTES, 'UTF-8')
                                        : '—' ?>
                                </td>

                                <td>
                                    S/ <?= number_format((float)$venta['subtotal'], 2) ?>
                                </td>

                                <td>
                                    S/ <?= number_format((float)$venta['igv'], 2) ?>
                                </td>

                                <td class="ventas-total">
                                    S/ <?= number_format((float)$venta['total'], 2) ?>
                                </td>

                                <td>
                                    <a
                                        href="<?= BASE_URL ?>public/ventas/comprobante?id=<?= (int)$venta['id_venta'] ?>"
                                        class="ventas-btn ventas-btn-info">
                                        Ver comprobante
                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="10" class="ventas-empty">

                                <div class="ventas-empty-icon">▤</div>

                                <strong>No se encontraron ventas</strong>

                                <p style="margin:7px 0 0;">
                                    No hay registros que coincidan con tu búsqueda.
                                </p>

                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="ventas-footer">

            <span>
                Registros encontrados:
                <strong><?= count($ventas ?? []) ?></strong>
            </span>

            <span>
                Librería | Gestión de ventas
            </span>

        </div>

    </div>

</div>
