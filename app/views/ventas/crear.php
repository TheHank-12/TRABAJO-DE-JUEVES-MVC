<?php $titulo_pagina = "Venta Directa en Mostrador"; ?>

<div style="max-width: 750px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2>🛒 Nueva Venta Directa (Caja POS)</h2>
        <a href="<?php echo BASE_URL; ?>ventas" class="btn btn-secondary">⬅️ Volver al Histórico</a>
    </div>

    <form action="<?php echo BASE_URL; ?>ventas/guardar-pos" method="POST" id="form-checkout">

        <!-- SECCIÓN CLIENTE -->
        <div style="background: #ffffff; padding: 1.2rem; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <label style="font-weight: 700; display: block; margin-bottom: 0.8rem; color: var(--secondary);">
                1. Selección o Registro de Cliente (*)
            </label>

            <div style="display: flex; gap: 1.5rem; margin-bottom: 1rem;">
                <label style="cursor: pointer;">
                    <input type="radio" name="tipo_cliente" value="EXISTENTE" checked onclick="toggleTipoCliente('EXISTENTE');">
                    👤 Cliente Existente / Público General
                </label>
                <label style="cursor: pointer;">
                    <input type="radio" name="tipo_cliente" value="NUEVO" onclick="toggleTipoCliente('NUEVO');">
                    ➕ Registrar Nuevo Cliente al Instante
                </label>
            </div>

            <div id="sec-cliente-existente">
                <select name="id_usuario_cliente" id="id_usuario_cliente" class="form-control">
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?php echo (int)$c['id_usuario']; ?>">
                            <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?> (DNI: <?php echo htmlspecialchars($c['dni']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="sec-cliente-nuevo" style="display: none; border-top: 1px dashed #ccc; padding-top: 1rem; margin-top: 0.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.8rem;">
                    <div class="form-group">
                        <label for="nuevo_dni">DNI (*):</label>
                        <input type="text" name="nuevo_dni" id="nuevo_dni" class="form-control" maxlength="8" placeholder="8 dígitos">
                    </div>
                    <div class="form-group">
                        <label for="nuevo_nombre">Nombres (*):</label>
                        <input type="text" name="nuevo_nombre" id="nuevo_nombre" class="form-control" placeholder="Ej. Juan Carlos">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="nuevo_apellido">Apellidos (*):</label>
                        <input type="text" name="nuevo_apellido" id="nuevo_apellido" class="form-control" placeholder="Ej. Pérez Gómez">
                    </div>
                    <div class="form-group">
                        <label for="nuevo_telefono">Teléfono (Opcional):</label>
                        <input type="text" name="nuevo_telefono" id="nuevo_telefono" class="form-control" placeholder="987654321">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN PRODUCTO -->
        <div style="background: #ffffff; padding: 1.2rem; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <label style="font-weight: 700; display: block; margin-bottom: 0.8rem; color: var(--secondary);">
                2. Selección de Producto (*)
            </label>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="id_libro">Libro Disponible (*):</label>
                    <select name="id_libro" id="id_libro" class="form-control" required>
                        <option value="">-- Seleccionar Libro --</option>
                        <?php foreach ($libros as $l): ?>
                            <option value="<?php echo (int)$l['id_libro']; ?>">
                                <?php echo htmlspecialchars($l['titulo']); ?> — S/ <?php echo number_format((float)$l['precio'], 2); ?> (Stock: <?php echo (int)$l['stock']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cantidad">Cantidad (*):</label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control" value="1" min="1" required>
                </div>
            </div>
        </div>

        <!-- SECCIÓN PAGO -->
        <div style="background: #ffffff; padding: 1.2rem; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <label style="font-weight: 700; display: block; margin-bottom: 0.8rem; color: var(--secondary);">
                3. Método de Pago (*)
            </label>

            <select name="metodo_pago" id="metodo_pago" class="form-control" required>
                <option value="EFECTIVO">💵 Efectivo en Mostrador</option>
                <option value="YAPE">📱 Yape (QR)</option>
                <option value="PLIN">📲 Plin (QR)</option>
                <option value="TARJETA">💳 Tarjeta de Crédito / Débito</option>
            </select>

            <input type="hidden" name="num_operacion" id="num_operacion_hidden" value="">
            <div id="resumen-pago-seleccionado" style="margin-top: 0.8rem; font-weight: 600; color: var(--primary);"></div>
        </div>

        <button type="submit" class="btn btn-success btn-lg btn-block" style="padding: 1rem; font-size: 1.1rem;">
            🧾 Procesar Cobro e Imprimir Boleta
        </button>
    </form>
</div>

<script>
function toggleTipoCliente(tipo) {
    const secExistente = document.getElementById('sec-cliente-existente');
    const secNuevo = document.getElementById('sec-cliente-nuevo');
    const inputDni = document.getElementById('nuevo_dni');
    const inputNombre = document.getElementById('nuevo_nombre');
    const inputApellido = document.getElementById('nuevo_apellido');

    if (tipo === 'NUEVO') {
        secExistente.style.display = 'none';
        secNuevo.style.display = 'block';
        inputDni.required = true;
        inputNombre.required = true;
        inputApellido.required = true;
    } else {
        secExistente.style.display = 'block';
        secNuevo.style.display = 'none';
        inputDni.required = false;
        inputNombre.required = false;
        inputApellido.required = false;
    }
}
</script>
