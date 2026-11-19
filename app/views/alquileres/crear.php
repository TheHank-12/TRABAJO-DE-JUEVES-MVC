<?php $titulo_pagina = "Registrar Alquiler"; ?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>alquileres" class="btn btn-secondary">⬅️ Volver a Alquileres</a>
</div>

<div class="form-card" style="max-width: 750px;">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Registrar Nuevo Alquiler de Libro</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger" style="margin-bottom: 1.2rem;">
            <div class="alerta-icono">⚠️</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>alquileres/crear" method="POST">
        <div style="background: #ffffff; padding: 1.2rem; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 1.2rem;">
            <label style="font-weight: 700; display: block; margin-bottom: 0.8rem; color: var(--secondary);">1. Cliente (*)</label>

            <div style="display: flex; gap: 1.5rem; margin-bottom: 1rem;">
                <label style="cursor: pointer;">
                    <input type="radio" name="tipo_cliente" value="EXISTENTE" checked onclick="toggleTipoCliente('EXISTENTE');">
                    👤 Cliente Existente
                </label>
                <label style="cursor: pointer;">
                    <input type="radio" name="tipo_cliente" value="NUEVO" onclick="toggleTipoCliente('NUEVO');">
                    ➕ Registrar Nuevo Cliente
                </label>
            </div>

            <div id="sec_cliente_existente">
                <select name="id_usuario" id="id_usuario" class="form-control">
                    <option value="">-- Seleccionar Cliente --</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?php echo (int)$c['id_usuario']; ?>">
                            <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?> (DNI: <?php echo htmlspecialchars($c['dni']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="sec_cliente_nuevo" style="display: none; border-top: 1px dashed #ccc; padding-top: 1rem; margin-top: 0.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.8rem;">
                    <div class="form-group">
                        <label for="nuevo_dni">DNI (*):</label>
                        <input type="text" name="nuevo_dni" id="nuevo_dni" class="form-control" maxlength="8" placeholder="8 dígitos">
                    </div>
                    <div class="form-group">
                        <label for="nuevo_nombre">Nombres (*):</label>
                        <input type="text" name="nuevo_nombre" id="nuevo_nombre" class="form-control" placeholder="Ej. Ana María">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="nuevo_apellido">Apellidos (*):</label>
                        <input type="text" name="nuevo_apellido" id="nuevo_apellido" class="form-control" placeholder="Ej. López Díaz">
                    </div>
                    <div class="form-group">
                        <label for="nuevo_telefono">Teléfono (Opcional):</label>
                        <input type="text" name="nuevo_telefono" id="nuevo_telefono" class="form-control" placeholder="987654321">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="id_libro">Libro Disponible (*):</label>
            <select name="id_libro" id="id_libro" class="form-control" required>
                <option value="">-- Seleccionar Libro --</option>
                <?php foreach ($libros as $l): ?>
                    <?php $selected = ((int)$l['id_libro'] === (int)($id_libro_pre ?? 0)) ? 'selected' : ''; ?>
                    <option value="<?php echo (int)$l['id_libro']; ?>" data-precio="<?php echo (float)$l['precio_alquiler']; ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($l['titulo']); ?> — S/ <?php echo number_format((float)$l['precio_alquiler'], 2); ?>/día (Stock: <?php echo (int)$l['stock']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="fecha_inicio">Fecha Inicio (*):</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="dias_alquiler">Días Alquiler (*):</label>
                <input type="number" name="dias_alquiler" id="dias_alquiler" class="form-control" min="1" value="5" required>
            </div>
            <div class="form-group">
                <label for="precio_alquiler_diario">Tarifa Diaria (S/):</label>
                <input type="text" id="precio_alquiler_diario" class="form-control" value="0.00" readonly style="background-color: #eee;">
            </div>
        </div>

        <div style="background-color: #fff8e7; border: 1px solid #ffe0b2; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.2rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem;">
                <span>Fecha Devolución Estimada:</span>
                <strong id="fecha_dev_esperada_preview" style="color: var(--primary);">-</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 1.1rem;">
                <span>Total a Cobrar por Alquiler:</span>
                <strong id="total_alquiler_preview" style="color: var(--success);">S/ 0.00</strong>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="metodo_pago">Método de Pago (*):</label>
                <select name="metodo_pago" id="metodo_pago" class="form-control" required>
                    <option value="EFECTIVO" selected>💵 Efectivo</option>
                    <option value="YAPE">📱 Yape</option>
                    <option value="PLIN">📲 Plin</option>
                    <option value="TARJETA">💳 Tarjeta</option>
                    <option value="TRANSFERENCIA">🏦 Transferencia</option>
                </select>
            </div>
            <div class="form-group">
                <label for="num_operacion">Nº Operación / Referencia:</label>
                <input type="text" name="num_operacion" id="num_operacion" class="form-control" placeholder="Ej. YP-99238">
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">📖 Confirmar Registro de Alquiler</button>
    </form>
</div>

<script>
function toggleTipoCliente(tipo) {
    const secExistente = document.getElementById("sec_cliente_existente");
    const secNuevo = document.getElementById("sec_cliente_nuevo");
    const inputDni = document.getElementById("nuevo_dni");
    const inputNombre = document.getElementById("nuevo_nombre");
    const inputApellido = document.getElementById("nuevo_apellido");
    const selectUsuario = document.getElementById("id_usuario");

    if (tipo === "NUEVO") {
        secExistente.style.display = "none";
        secNuevo.style.display = "block";
        inputDni.required = true;
        inputNombre.required = true;
        inputApellido.required = true;
        selectUsuario.required = false;
        selectUsuario.value = "";
    } else {
        secExistente.style.display = "block";
        secNuevo.style.display = "none";
        inputDni.required = false;
        inputNombre.required = false;
        inputApellido.required = false;
        selectUsuario.required = true;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const selectLibro = document.getElementById("id_libro");
    const inputPrecioDiario = document.getElementById("precio_alquiler_diario");
    const inputDias = document.getElementById("dias_alquiler");
    const inputFechaInicio = document.getElementById("fecha_inicio");
    const previewFecha = document.getElementById("fecha_dev_esperada_preview");
    const previewTotal = document.getElementById("total_alquiler_preview");

    function calcularAlquiler() {
        let precio = 0;
        if (selectLibro && selectLibro.selectedIndex > 0) {
            const option = selectLibro.options[selectLibro.selectedIndex];
            precio = parseFloat(option.getAttribute("data-precio")) || 0;
            inputPrecioDiario.value = precio.toFixed(2);
        } else {
            inputPrecioDiario.value = "0.00";
        }

        const dias = parseInt(inputDias.value) || 0;
        const total = precio * dias;
        previewTotal.textContent = "S/ " + total.toFixed(2);

        if (inputFechaInicio.value && dias > 0) {
            const fecha = new Date(inputFechaInicio.value + "T00:00:00");
            fecha.setDate(fecha.getDate() + dias);
            const dd = String(fecha.getDate()).padStart(2, '0');
            const mm = String(fecha.getMonth() + 1).padStart(2, '0');
            const yyyy = fecha.getFullYear();
            previewFecha.textContent = `${dd}/${mm}/${yyyy}`;
        } else {
            previewFecha.textContent = "-";
        }
    }

    if (selectLibro) selectLibro.addEventListener("change", calcularAlquiler);
    if (inputDias) inputDias.addEventListener("input", calcularAlquiler);
    if (inputFechaInicio) inputFechaInicio.addEventListener("change", calcularAlquiler);

    toggleTipoCliente('EXISTENTE');
    calcularAlquiler();
});
</script>
