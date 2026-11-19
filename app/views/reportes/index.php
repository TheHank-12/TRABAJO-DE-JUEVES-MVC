
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>📊 Panel de Reportes y Control Administrativo</h2>
    <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
        <a href="<?php echo BASE_URL; ?>public/reportes/multas" class="btn btn-secondary">⚠️ Control de Multas</a>
        <a href="<?php echo BASE_URL; ?>public/reportes/imprimir" class="btn btn-success">🖨️ Reporte Ejecutivo Consolidado</a>
    </div>
</div>

<!-- Tarjetas de Resumen -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="form-card" style="max-width: 100%; padding: 1.2rem; border-left: 4px solid var(--primary);">
        <small style="color: var(--text-muted); font-weight: 600;">INGRESOS POR VENTAS</small>
        <h3 style="margin: 0.4rem 0; color: var(--primary);">S/ <?php echo number_format($resumen['ingresos_ventas'], 2); ?></h3>
        <span style="font-size: 0.85rem;"><?php echo $resumen['total_ventas']; ?> comprobante(s) emitidos</span>
    </div>

    <div class="form-card" style="max-width: 100%; padding: 1.2rem; border-left: 4px solid #198754;">
        <small style="color: var(--text-muted); font-weight: 600;">INGRESOS POR ALQUILERES</small>
        <h3 style="margin: 0.4rem 0; color: #198754;">S/ <?php echo number_format($resumen['ingresos_alquileres'], 2); ?></h3>
        <span style="font-size: 0.85rem;"><?php echo $resumen['total_alquileres']; ?> préstamo(s) registrados</span>
    </div>

    <div class="form-card" style="max-width: 100%; padding: 1.2rem; border-left: 4px solid #dc3545;">
        <small style="color: var(--text-muted); font-weight: 600;">MULTAS PENDIENTES</small>
        <h3 style="margin: 0.4rem 0; color: #dc3545;">S/ <?php echo number_format($resumen['monto_pendiente'], 2); ?></h3>
        <span style="font-size: 0.85rem;"><?php echo $resumen['multas_pendientes']; ?> multa(s) por cobrar</span>
    </div>

    <div class="form-card" style="max-width: 100%; padding: 1.2rem; border-left: 4px solid #fd7e14;">
        <small style="color: var(--text-muted); font-weight: 600;">ALERTA DE STOCK CRÍTICO</small>
        <h3 style="margin: 0.4rem 0; color: #fd7e14;"><?php echo $resumen['bajo_stock']; ?> libro(s)</h3>
        <span style="font-size: 0.85rem;">Con 3 unidades o menos</span>
    </div>
</div>

<!-- Libros con bajo stock -->
<h3 style="margin-bottom: 0.8rem;">📉 Libros con Stock Crítico (≤ 3 unidades)</h3>
<div class="table-responsive" style="margin-bottom: 2rem;">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>ISBN</th>
                <th>Stock Actual</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($libros_bajo_stk)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Todos los libros cuentan con stock suficiente.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($libros_bajo_stk as $lib): ?>
                    <tr>
                        <td><?php echo $lib['id_libro']; ?></td>
                        <td><strong><?php echo htmlspecialchars($lib['titulo']); ?></strong></td>
                        <td><?php echo htmlspecialchars($lib['isbn']); ?></td>
                        <td><strong style="color: var(--danger);"><?php echo $lib['stock']; ?> un.</strong></td>
                        <td><span class="user-role-badge badge-admin"><?php echo htmlspecialchars($lib['estado']); ?></span></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>public/kardex/registrar" class="btn btn-primary btn-sm">➕ Reponer en Kárdex</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Últimas Ventas -->
<h3 style="margin-bottom: 0.8rem;">🧾 Últimas Ventas Registradas</h3>
<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>Nº Comprobante</th>
                <th>Fecha</th>
                <th>Cliente / DNI</th>
                <th>Método Pago</th>
                <th>Total S/</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ultimas_ventas)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No hay ventas registradas aún.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($ultimas_ventas as $v): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($v['num_comprobante']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($v['fecha_venta'])); ?></td>
                        <td><?php echo htmlspecialchars($v['nombre'] . ' ' . $v['apellido']); ?> (DNI: <?php echo htmlspecialchars($v['dni']); ?>)</td>
                        <td><?php echo htmlspecialchars($v['metodo_pago']); ?></td>
                        <td><strong>S/ <?php echo number_format($v['total'], 2); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
