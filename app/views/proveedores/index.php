<?php include_once ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>🚛 Administración de Proveedores</h2>
    <a href="<?php echo BASE_URL; ?>public/proveedores/crear" class="btn btn-success">➕ Registrar Proveedor</a>
</div>

<div class="catalogo-header" style="margin-bottom: 1rem;">
    <div class="buscador-box">
        <input type="text" id="input-search-live" class="input-search" placeholder="Filtrar por RUC/DNI, Razón Social o Contacto...">
    </div>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>RUC / DNI</th>
                <th>Razón Social</th>
                <th>Contacto</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Libros Suministrados</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($proveedores)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No hay proveedores registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($proveedores as $prov): ?>
                    <tr>
                        <td><?php echo $prov['id_proveedor']; ?></td>
                        <td><strong><?php echo htmlspecialchars($prov['ruc_dni']); ?></strong></td>
                        <td><?php echo htmlspecialchars($prov['razon_social']); ?></td>
                        <td><?php echo htmlspecialchars($prov['contacto'] ? $prov['contacto'] : '-'); ?></td>
                        <td><?php echo htmlspecialchars($prov['telefono'] ? $prov['telefono'] : '-'); ?></td>
                        <td><?php echo htmlspecialchars($prov['email'] ? $prov['email'] : '-'); ?></td>
                        <td><span class="user-role-badge badge-client"><?php echo $prov['total_libros']; ?> libros</span></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="<?php echo BASE_URL; ?>public/proveedores/editar?id=<?php echo $prov['id_proveedor']; ?>" class="btn btn-secondary btn-sm" title="Editar">✏️</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmarAccion('<?php echo BASE_URL; ?>public/proveedores/eliminar?id=<?php echo $prov['id_proveedor']; ?>', '¿Está seguro de eliminar al proveedor <?php echo htmlspecialchars($prov['razon_social']); ?>?');">🗑️</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modal-confirmacion">
    <div class="modal-content">
        <h3 style="margin-bottom: 1rem; color: var(--primary);">Confirmación de Eliminación</h3>
        <p id="modal-texto-confirmacion">¿Está seguro de realizar esta acción?</p>
        <div class="modal-acciones">
            <button type="button" class="btn btn-secondary" onclick="cerrarModal();">Cancelar</button>
            <a id="modal-btn-aceptar" href="#" class="btn btn-danger">Eliminar</a>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . '/app/views/layouts/footer.php'; ?>