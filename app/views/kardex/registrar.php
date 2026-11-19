<?php include_once ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>public/kardex" class="btn btn-secondary">⬅️ Volver al Kárdex</a>
</div>

<div class="form-card">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Registrar Movimiento de Inventario</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>public/kardex/registrar" method="POST">
        <div class="form-group">
            <label for="id_libro">Seleccionar Libro (*):</label>
            <select name="id_libro" id="id_libro" class="form-control" required>
                <option value="">-- Seleccionar --</option>
                <?php foreach ($libros as $l): ?>
                    <option value="<?php echo $l['id_libro']; ?>">
                        <?php echo htmlspecialchars($l['titulo']); ?> (Stock Actual: <?php echo $l['stock']; ?> un.)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="tipo_movimiento">Tipo de Movimiento (*):</label>
                <select name="tipo_movimiento" id="tipo_movimiento" class="form-control" required>
                    <option value="ENTRADA">ENTRADA (Ingreso de lote / Compra)</option>
                    <option value="SALIDA_VENTA">SALIDA VENTA (Despacho)</option>
                    <option value="SALIDA_ALQUILER">SALIDA ALQUILER (Préstamo)</option>
                    <option value="DEVOLUCION">DEVOLUCION (Retorno de Alquiler)</option>
                    <option value="AJUSTE">AJUSTE (Corrección de inventario)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="cantidad">Cantidad de Unidades (*):</label>
                <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" value="1" required>
            </div>
        </div>

        <div class="form-group">
            <label for="motivo">Motivo / Justificación:</label>
            <textarea name="motivo" id="motivo" class="form-control" rows="3" placeholder="Ej. Recepción de factura F001-445 / Ajuste por deterioro de ejemplar..." required></textarea>
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">💾 Confirmar y Registrar Movimiento</button>
    </form>
</div>

<?php include_once ROOT_PATH . '/app/views/layouts/footer.php'; ?>