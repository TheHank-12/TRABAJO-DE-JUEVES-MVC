<?php include_once ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>public/proveedores" class="btn btn-secondary">⬅️ Volver a Proveedores</a>
</div>

<div class="form-card">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Registrar Nuevo Proveedor</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>public/proveedores/guardar" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="ruc_dni">RUC / DNI (*):</label>
                <input type="text" name="ruc_dni" id="ruc_dni" class="form-control" maxlength="20" value="<?php echo htmlspecialchars($datos['ruc_dni']); ?>" required>
            </div>
            <div class="form-group">
                <label for="razon_social">Razón Social / Empresa (*):</label>
                <input type="text" name="razon_social" id="razon_social" class="form-control" value="<?php echo htmlspecialchars($datos['razon_social']); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="contacto">Contacto Responsable:</label>
                <input type="text" name="contacto" id="contacto" class="form-control" value="<?php echo htmlspecialchars($datos['contacto']); ?>">
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono / Celular:</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="<?php echo htmlspecialchars($datos['telefono']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($datos['email']); ?>">
        </div>

        <div class="form-group">
            <label for="direccion">Dirección Comercial:</label>
            <input type="text" name="direccion" id="direccion" class="form-control" value="<?php echo htmlspecialchars($datos['direccion']); ?>">
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">💾 Guardar Proveedor</button>
    </form>
</div>

<?php include_once ROOT_PATH . '/app/views/layouts/footer.php'; ?>