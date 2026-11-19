<?php include_once ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>📦 Kárdex - Historial de Entradas y Salidas</h2>
    <a href="<?php echo BASE_URL; ?>public/kardex/registrar" class="btn btn-success">➕ Registrar Movimiento Manual</a>
</div>

<!-- Filtros de Kárdex -->
<div class="form-card" style="max-width: 100%; padding: 1.2rem; margin-bottom: 1.5rem;">
    <form action="<?php echo BASE_URL; ?>public/kardex" method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label for="id_libro">Filtrar por Libro:</label>
            <select name="id_libro" id="id_libro" class="form-control">
                <option value="0">-- Todos los Libros --</option>
                <?php foreach ($libros as $l): ?>
                    <option value="<?php echo $l['id_libro']; ?>" <?php echo $filtro_libro === (int)$l['id_libro'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($l['titulo']); ?> (ISBN: <?php echo htmlspecialchars($l['isbn']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="tipo">Tipo de Movimiento:</label>
            <select name="tipo" id="tipo" class="form-control">
                <option value="">-- Todos los Tipos --</option>
                <option value="ENTRADA" <?php echo $filtro_tipo === 'ENTRADA' ? 'selected' : ''; ?>>ENTRADA</option>
                <option value="SALIDA_VENTA" <?php echo $filtro_tipo === 'SALIDA_VENTA' ? 'selected' : ''; ?>>SALIDA VENTA</option>
                <option value="SALIDA_ALQUILER" <?php echo $filtro_tipo === 'SALIDA_ALQUILER' ? 'selected' : ''; ?>>SALIDA ALQUILER</option>
                <option value="DEVOLUCION" <?php echo $filtro_tipo === 'DEVOLUCION' ? 'selected' : ''; ?>>DEVOLUCION</option>
                <option value="AJUSTE" <?php echo $filtro_tipo === 'AJUSTE' ? 'selected' : ''; ?>>AJUSTE</option>
            </select>
        </div>

        <div style="display: flex; gap: 5px;">
            <button type="submit" class="btn btn-primary btn-block">🔍 Filtrar</button>
            <?php if ($filtro_libro > 0 || !empty($filtro_tipo)): ?>
                <a href="<?php echo BASE_URL; ?>public/kardex" class="btn btn-danger" title="Limpiar Filtros">✖</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha y Hora</th>
                <th>Libro / ISBN</th>
                <th>Tipo Movimiento</th>
                <th>Cantidad</th>
                <th>Stock Previo</th>
                <th>Stock Nuevo</th>
                <th>Motivo / Observación</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($movimientos)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No se encontraron registros en el Kárdex con los filtros aplicados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($movimientos as $k): ?>
                    <tr>
                        <td><?php echo $k['id_kardex']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($k['fecha_movimiento'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($k['libro_titulo']); ?></strong><br>
                            <small style="color: var(--text-muted);">ISBN: <?php echo htmlspecialchars($k['isbn']); ?></small>
                        </td>
                        <td>
                            <?php 
                            $badge_class = 'badge-client';
                            if (strpos($k['tipo_movimiento'], 'SALIDA') !== false) {
                                $badge_class = 'badge-admin';
                            } elseif ($k['tipo_movimiento'] === 'ENTRADA' || $k['tipo_movimiento'] === 'DEVOLUCION') {
                                $badge_class = 'badge-client';
                            }
                            ?>
                            <span class="user-role-badge <?php echo $badge_class; ?>">
                                <?php echo htmlspecialchars($k['tipo_movimiento']); ?>
                            </span>
                        </td>
                        <td><strong><?php echo $k['cantidad']; ?> un.</strong></td>
                        <td><?php echo $k['stock_anterior']; ?></td>
                        <td><strong style="color: var(--primary);"><?php echo $k['stock_actual']; ?></strong></td>
                        <td><?php echo htmlspecialchars($k['motivo'] ? $k['motivo'] : 'Sin observación'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once ROOT_PATH . '/app/views/layouts/footer.php'; ?>