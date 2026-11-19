<?php
$esPersonal = function_exists('es_personal') ? es_personal() : false;
$titulo_pagina = $esPersonal ? "Historial de Ventas" : "Mis Compras";
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2><?php echo $esPersonal ? '💵 Registro e Histórico de Ventas' : '🛍️ Mis Compras'; ?></h2>
    <?php if ($esPersonal): ?>
        <a href="<?php echo BASE_URL; ?>public/ventas/crear" class="btn btn-success">➕ Nueva Venta Directa en Mostrador</a>
    <?php endif; ?>
</div>

<div class="catalogo-header" style="margin-bottom: 1rem;">
    <div class="buscador-box">
        <input type="text" id="input-search-live" class="input-search" placeholder="<?php echo $esPersonal ? 'Filtrar por comprobante, cliente o DNI...' : 'Filtrar por Nº de comprobante...'; ?>" value="<?php echo htmlspecialchars($busqueda ?? ''); ?>">
    </div>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>Nº Comprobante</th>
                <th>Fecha y Hora</th>
                <?php if ($esPersonal): ?>
                    <th>Cliente / DNI</th>
                <?php endif; ?>
                <th>Método Pago</th>
                <th>Subtotal</th>
                <th>IGV (18%)</th>
                <th>Total S/</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ventas)): ?>
                <tr>
                    <td colspan="<?php echo $esPersonal ? '8' : '7'; ?>" style="text-align: center;">
                        <?php echo $esPersonal ? 'No se registraron ventas en el sistema.' : 'Aún no tienes compras registradas.'; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($v['num_comprobante']); ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($v['fecha_venta'])); ?></td>
                        <?php if ($esPersonal): ?>
                            <td>
                                <?php echo htmlspecialchars($v['nombre'] . ' ' . $v['apellido']); ?><br>
                                <small style="color: var(--text-muted);">DNI: <?php echo htmlspecialchars($v['dni']); ?></small>
                            </td>
                        <?php endif; ?>
                        <td>
                            <span class="badge-pago <?php echo strtolower($v['metodo_pago']); ?>">
                                <?php echo htmlspecialchars($v['metodo_pago']); ?>
                            </span>
                            <?php if (!empty($v['num_operacion'])): ?>
                                <br><small style="font-size: 0.75rem; color: var(--text-muted);">Ref: <?php echo htmlspecialchars($v['num_operacion']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>S/ <?php echo number_format((float)$v['subtotal'], 2); ?></td>
                        <td>S/ <?php echo number_format((float)$v['igv'], 2); ?></td>
                        <td><strong style="color: var(--primary); font-size: 1.05rem;">S/ <?php echo number_format((float)$v['total'], 2); ?></strong></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>public/ventas/comprobante?id=<?php echo (int)$v['id_venta']; ?>" class="btn btn-secondary btn-sm" title="Ver / Imprimir Boleta">📄 Boleta</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
