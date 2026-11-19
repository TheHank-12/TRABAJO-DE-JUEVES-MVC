<?php $titulo_pagina = "Gestión de Multas"; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>⚠️ Control de Multas por Morosidad</h2>
    <a href="<?php echo BASE_URL; ?>alquileres" class="btn btn-secondary">⬅️ Volver a Alquileres</a>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>ID Multa</th>
                <th>Cliente / DNI</th>
                <th>Libro en Morosidad</th>
                <th>Días Retraso</th>
                <th>Tarifa Diaria</th>
                <th>Total Multa S/</th>
                <th>Estado</th>
                <th>Fecha Pago</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($multas)): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">No hay multas registradas en el sistema.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($multas as $m): ?>
                    <tr>
                        <td>#<?php echo (int)$m['id_multa']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($m['usuario_nombre'] . ' ' . $m['usuario_apellido']); ?></strong><br>
                            <small style="color: var(--text-muted);">DNI: <?php echo htmlspecialchars($m['usuario_dni']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($m['libro_titulo']); ?></td>
                        <td><strong><?php echo (int)$m['dias_atraso']; ?> días</strong></td>
                        <td>S/ <?php echo number_format((float)$m['monto_diario'], 2); ?></td>
                        <td><strong style="color: var(--danger); font-size: 1.05rem;">S/ <?php echo number_format((float)$m['monto_total'], 2); ?></strong></td>
                        <td>
                            <?php if ($m['estado'] === 'Pendiente'): ?>
                                <span style="background-color: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">PENDIENTE</span>
                            <?php else: ?>
                                <span style="background-color: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">PAGADA</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo !empty($m['fecha_pago']) ? date('d/m/Y H:i', strtotime($m['fecha_pago'])) : '-'; ?></td>
                        <td>
                            <?php if ($m['estado'] === 'Pendiente'): ?>
                                <button type="button" class="btn btn-success btn-sm" onclick="confirmarAccion('<?php echo BASE_URL; ?>alquileres/multas?cobrar_id=<?php echo (int)$m['id_multa']; ?>', '¿Confirmar cobro de multa por S/ <?php echo number_format((float)$m['monto_total'], 2); ?>?');">💰 Cobrar</button>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.8rem;">Saldado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modal-confirmacion">
    <div class="modal-content">
        <h3 style="margin-bottom: 1rem; color: var(--primary);">Confirmar Cobro</h3>
        <p id="modal-texto-confirmacion">¿Desea registrar el cobro de esta multa?</p>
        <div class="modal-acciones">
            <button type="button" class="btn btn-secondary" onclick="cerrarModal();">Cancelar</button>
            <a id="modal-btn-aceptar" href="#" class="btn btn-success">Procesar Cobro</a>
        </div>
    </div>
</div>
