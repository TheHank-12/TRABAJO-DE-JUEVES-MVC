
<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .form-card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        h3 {
            page-break-after: avoid;
        }
    }
</style>

<div class="no-print" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <a href="<?php echo BASE_URL; ?>public/reportes" class="btn btn-secondary">
        ⬅️ Volver a Reportes
    </a>

    <button type="button" onclick="window.print();" class="btn btn-success">
        🖨️ Imprimir / Guardar como PDF
    </button>
</div>

<div class="form-card" style="max-width: 100%; padding: 2rem; background: #fff;">

    <!-- Encabezado -->
    <div style="text-align: center; border-bottom: 2px solid var(--secondary); padding-bottom: 1rem; margin-bottom: 1.5rem;">

        <h1 style="color: var(--primary);">
            LIBRERÍA VALLEJO
        </h1>

        <h2 style="font-size: 1.2rem;">
            REPORTE CONSOLIDADO DE OPERACIONES
        </h2>

        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Fecha de emisión:
            <?php
            echo htmlspecialchars(
                (string) $fecha_generacion,
                ENT_QUOTES,
                'UTF-8'
            );
            ?>
            | Sistema de Gestión MVC
        </p>

    </div>

    <!-- Ventas -->
    <h3 style="margin-bottom: 0.8rem; color: var(--secondary);">
        1. Registro Histórico de Ventas
    </h3>

    <div class="table-responsive" style="box-shadow: none; border: 1px solid #ccc; margin-bottom: 2rem;">

        <table class="tabla-custom">
            <thead>
                <tr>
                    <th>Nº Comprobante</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Método Pago</th>
                    <th>Total S/</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($ventas)): ?>

                    <tr>
                        <td colspan="5" style="text-align: center;">
                            No hay ventas registradas.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($ventas as $v): ?>

                        <tr>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    (string) ($v['num_comprobante'] ?? ''),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo !empty($v['fecha_venta'])
                                    ? date('d/m/Y H:i', strtotime($v['fecha_venta']))
                                    : '-';
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    ($v['nombre'] ?? '') . ' ' . ($v['apellido'] ?? ''),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                                (DNI:
                                <?php
                                echo htmlspecialchars(
                                    (string) ($v['dni'] ?? ''),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                                )
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    (string) ($v['metodo_pago'] ?? ''),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </td>

                            <td>
                                <strong>
                                    S/ <?php echo number_format((float) $v['total'], 2); ?>
                                </strong>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>
            </tbody>
        </table>

    </div>

    <!-- Alquileres -->
    <h3 style="margin-bottom: 0.8rem; color: var(--secondary);">
        2. Registro Histórico de Alquileres y Préstamos
    </h3>

    <div class="table-responsive" style="box-shadow: none; border: 1px solid #ccc;">

        <table class="tabla-custom">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Libro</th>
                    <th>F. Inicio</th>
                    <th>F. Dev. Esperada</th>
                    <th>Monto S/</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($alquileres)): ?>

                    <tr>
                        <td colspan="7" style="text-align: center;">
                            No hay alquileres registrados.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($alquileres as $a): ?>

                        <tr>
                            <td>
                                #<?php echo (int) $a['id_alquiler']; ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    ($a['nombre'] ?? '') . ' ' . ($a['apellido'] ?? ''),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    (string) ($a['titulo'] ?? ''),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo !empty($a['fecha_inicio'])
                                    ? date('d/m/Y', strtotime($a['fecha_inicio']))
                                    : '-';
                                ?>
                            </td>

                            <td>
                                <?php
                                echo !empty($a['fecha_dev_esperada'])
                                    ? date('d/m/Y', strtotime($a['fecha_dev_esperada']))
                                    : '-';
                                ?>
                            </td>

                            <td>
                                S/ <?php echo number_format((float) $a['monto_alquiler'], 2); ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    (string) ($a['estado'] ?? ''),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>
            </tbody>
        </table>

    </div>

    <!-- Pie de página -->
    <div style="margin-top: 3rem; text-align: center; border-top: 1px solid #eee; padding-top: 1rem; font-size: 0.85rem; color: var(--text-muted);">

        <p>
            Documento generado por el Sistema de Gestión de Librería.
            Exclusivo para auditoría interna.
        </p>

    </div>

</div>
