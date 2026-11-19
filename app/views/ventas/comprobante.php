<?php $titulo_pagina = "Comprobante de Venta"; ?>

<div class="no-print" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?php echo BASE_URL; ?>ventas" class="btn btn-secondary">⬅️ Regresar a Ventas</a>
    <button onclick="window.print();" class="btn btn-success">🖨️ Imprimir Boleta de Venta</button>
</div>

<div class="form-card" style="max-width: 750px; background: #fff; padding: 2.5rem; border: 1px solid var(--border-color);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--secondary); padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <img src="<?php echo BASE_URL; ?>img/logo.png" alt="Logo Librería" style="height: 60px;" onerror="this.src='<?php echo BASE_URL; ?>img/no-cover.png'">
            <div>
                <h2 style="margin: 0; color: var(--secondary);">LIBRERÍA VALLEJO S.A.C.</h2>
                <small style="color: var(--text-muted);">RUC: 20123456789</small><br>
                <small style="color: var(--text-muted);">Av. Javier Prado Este 1234, Lima - Perú</small><br>
                <small style="color: var(--text-muted);">Teléfono: (01) 423-8900 | contacto@libreriaperu.pe</small>
            </div>
        </div>

        <div style="border: 2px solid var(--primary); padding: 0.8rem 1.2rem; border-radius: var(--radius); text-align: center; background: #fff8f8;">
            <h4 style="margin: 0; color: var(--primary);">BOLETA DE VENTA</h4>
            <span style="font-size: 0.85rem; color: var(--text-muted);">ELECTRÓNICA</span>
            <div style="font-size: 1.1rem; font-weight: 700; margin-top: 4px; font-family: monospace;"><?php echo htmlspecialchars($venta['num_comprobante']); ?></div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; background: var(--bg-light); padding: 1rem; border-radius: var(--radius);">
        <div>
            <p style="margin-bottom: 4px;"><strong>ADQUIRIENTE / CLIENTE:</strong></p>
            <p style="margin-bottom: 4px;"><?php echo htmlspecialchars($venta['nombre'] . ' ' . $venta['apellido']); ?></p>
            <p style="margin-bottom: 4px;"><strong>DNI / RUC:</strong> <?php echo htmlspecialchars($venta['dni']); ?></p>
            <p style="margin: 0;"><strong>Dirección:</strong> <?php echo htmlspecialchars($venta['direccion'] ? $venta['direccion'] : 'Lima, Perú'); ?></p>
        </div>
        <div>
            <p style="margin-bottom: 4px;"><strong>FECHA DE EMISIÓN:</strong> <?php echo date('d/m/Y H:i:s', strtotime($venta['fecha_venta'])); ?></p>
            <p style="margin-bottom: 4px;"><strong>FORMA DE PAGO:</strong> <?php echo htmlspecialchars($venta['metodo_pago']); ?></p>
            <?php if (!empty($venta['num_operacion'])): ?>
                <p style="margin-bottom: 4px;"><strong>Nº OPERACIÓN:</strong> <?php echo htmlspecialchars($venta['num_operacion']); ?></p>
            <?php endif; ?>
            <p style="margin: 0;"><strong>MONEDA:</strong> Soles Peruanos (S/)</p>
        </div>
    </div>

    <div class="table-responsive" style="box-shadow: none; border-radius: 0; margin-bottom: 1.5rem;">
        <table class="tabla-custom">
            <thead>
                <tr>
                    <th style="width: 50px;">Cant.</th>
                    <th>Descripción / ISBN</th>
                    <th style="text-align: right;">P. Unitario</th>
                    <th style="text-align: right;">Importe S/</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $d): ?>
                    <tr>
                        <td style="text-align: center; font-weight: 700;"><?php echo (int)$d['cantidad']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($d['titulo']); ?></strong><br>
                            <small style="color: var(--text-muted);">ISBN: <?php echo htmlspecialchars($d['isbn']); ?></small>
                        </td>
                        <td style="text-align: right;">S/ <?php echo number_format((float)$d['precio_unitario'], 2); ?></td>
                        <td style="text-align: right;">S/ <?php echo number_format((float)$d['subtotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
        <div style="width: 280px; font-size: 0.95rem;">
            <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                <span>Op. Gravada (Subtotal):</span>
                <strong>S/ <?php echo number_format((float)$venta['subtotal'], 2); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                <span>IGV (18%):</span>
                <strong>S/ <?php echo number_format((float)$venta['igv'], 2); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-top: 2px solid var(--secondary); font-size: 1.2rem; color: var(--primary); font-weight: 700;">
                <span>TOTAL A PAGAR:</span>
                <span>S/ <?php echo number_format((float)$venta['total'], 2); ?></span>
            </div>
        </div>
    </div>

    <div style="text-align: center; border-top: 1px dashed var(--border-color); padding-top: 1rem; font-size: 0.85rem; color: var(--text-muted);">
        <p>¡Gracias por su compra en Librería Vallejo!</p>
        <p>Representación impresa de la Boleta de Venta Electrónica.</p>
    </div>
</div>
