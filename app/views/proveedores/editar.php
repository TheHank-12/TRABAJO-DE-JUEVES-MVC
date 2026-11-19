
<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>public/proveedores" class="btn btn-secondary">
        ⬅️ Volver a Proveedores
    </a>
</div>

<div class="form-card">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">
        Editar Proveedor
    </h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>public/proveedores/actualizar" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8'); ?>"
        >

        <input
            type="hidden"
            name="id_proveedor"
            value="<?php echo (int)($prov['id_proveedor'] ?? 0); ?>"
        >

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="ruc_dni">RUC / DNI (*):</label>
                <input
                    type="text"
                    name="ruc_dni"
                    id="ruc_dni"
                    class="form-control"
                    value="<?php echo htmlspecialchars($prov['ruc_dni'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="razon_social">Razón Social (*):</label>
                <input
                    type="text"
                    name="razon_social"
                    id="razon_social"
                    class="form-control"
                    value="<?php echo htmlspecialchars($prov['razon_social'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="contacto">Contacto Responsable:</label>
                <input
                    type="text"
                    name="contacto"
                    id="contacto"
                    class="form-control"
                    value="<?php echo htmlspecialchars($prov['contacto'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input
                    type="text"
                    name="telefono"
                    id="telefono"
                    class="form-control"
                    value="<?php echo htmlspecialchars($prov['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>
        </div>

        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input
                type="email"
                name="email"
                id="email"
                class="form-control"
                value="<?php echo htmlspecialchars($prov['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="form-group">
            <label for="direccion">Dirección Comercial:</label>
            <input
                type="text"
                name="direccion"
                id="direccion"
                class="form-control"
                value="<?php echo htmlspecialchars($prov['direccion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">
            💾 Actualizar Proveedor
        </button>
    </form>
</div>
