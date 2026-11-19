<?php $titulo_pagina = "Devolución de Libro"; ?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>alquileres" class="btn btn-secondary">⬅️ Volver a Alquileres</a>
</div>

<div class="form-card" style="max-width: 650px;">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Procesar Devolución de Libro</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger" style="margin-bottom: 1rem;">
            <div class="alerta-icono">⚠️</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <div style="background: var(--bg-light); padding: 1rem; border-radius: var(--radius); margin-bottom: 1.2rem;">
        <p style="margin-bottom: 4px;"><strong>Cliente:</strong> <?php echo htmlspecialchars($alquiler['usuario_nombre'] . ' ' . $alquiler['usuario_apellido']); ?> (DNI: <?php echo htmlspecialchars($alquiler['usuario_dni']); ?>)</p>
        <p style="margin-bottom: 4px;"><strong>Libro:</strong> <?php echo htmlspecialchars($alquiler['libro_titulo']); ?> (ISBN: <?php echo htmlspecialchars($alquiler['libro_isbn']); ?>)</p>
        <p style="margin-bottom: 4px;"><strong>Fecha Inicio:</strong> <?php echo date('d/m/Y', strtotime($alquiler['fecha_inicio'])); ?></p>
        <p style="margin: 0;"><strong>Devolución Esperada:</strong> <?php echo date('d/m/Y', strtotime($alquiler['fecha_dev_esperada'])); ?></p>
    </div>

    <form action="<?php echo BASE_URL; ?>alquileres/devolver?id=<?php echo (int)$alquiler['id_alquiler']; ?>" method="POST">
        <div class="form-group">
            <label for="fecha_dev_real">Fecha de Devolución Real (*):</label>
            <input type="date" name="fecha_dev_real" id="fecha_dev_real" class="form-control" value="<?php echo htmlspecialchars($fecha_hoy); ?>" required>
        </div>

        <?php if ($dias_demora > 0): ?>
            <div class="alerta-box alerta-warning" style="margin-bottom: 1.2rem;">
                <div class="alerta-icono">⚠️</div>
                <div class="alerta-contenido">
                    <strong>¡ALERTA DE MOROSIDAD!</strong><br>
                    Este préstamo cuenta con <strong><?php echo (int)$dias_demora; ?> días de atraso</strong>.<br>
                    Multa generada (S/ <?php echo number_format((float)$monto_diario_mora, 2); ?>/día): <strong style="font-size: 1.2rem;">S/ <?php echo number_format((float)$total_multa, 2); ?></strong>
                </div>
            </div>

            <div class="form-group" style="background: #fff; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: var(--radius);">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="cobrar_multa_ahora" value="1" checked style="width: 18px; height: 18px;">
                    <span>Cobrar y registrar multa de <strong>S/ <?php echo number_format((float)$total_multa, 2); ?></strong> de inmediato</span>
                </label>
            </div>
        <?php else: ?>
            <div class="alerta-box alerta-success" style="margin-bottom: 1.2rem;">
                <div class="alerta-icono">✅</div>
                <div class="alerta-contenido">Devolución dentro del plazo contratado. Sin multas por mora.</div>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">📥 Recepcionar Libro y Finalizar</button>
    </form>
</div>
