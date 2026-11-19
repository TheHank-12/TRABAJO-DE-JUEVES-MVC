
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>🚛 Administración de Proveedores</h2>
    <a href="<?php echo BASE_URL; ?>public/proveedores/crear" class="btn btn-success">
        ➕ Registrar Proveedor
    </a>
</div>

<div class="catalogo-header" style="margin-bottom: 1rem;">
    <div class="buscador-box">
        <input
            type="text"
            id="input-search-live"
            class="input-search"
            placeholder="Filtrar por RUC/DNI, Razón Social o Contacto..."
        >
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
                    <td colspan="8" style="text-align: center;">
                        No hay proveedores registrados.
                    </td>
                </tr>
            <?php else: ?>

                <?php foreach ($proveedores as $prov): ?>
                    <tr>
                        <td><?php echo (int)$prov['id_proveedor']; ?></td>

                        <td>
                            <strong>
                                <?php echo htmlspecialchars($prov['ruc_dni'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </strong>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($prov['razon_social'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($prov['contacto'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($prov['telefono'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($prov['email'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <span class="user-role-badge badge-client">
                                <?php echo (int)$prov['total_libros']; ?> libros
                            </span>
                        </td>

                        <td>
                            <div style="display: flex; gap: 5px;">

                                <a
                                    href="<?php echo BASE_URL; ?>public/proveedores/editar?id=<?php echo (int)$prov['id_proveedor']; ?>"
                                    class="btn btn-secondary btn-sm"
                                    title="Editar"
                                >✏️</a>

                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm"
                                    data-id="<?php echo (int)$prov['id_proveedor']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($prov['razon_social'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    onclick="prepararEliminacion(this)"
                                >🗑️</button>

                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal de confirmación -->
<div class="modal-overlay" id="modal-confirmacion">
    <div class="modal-content">

        <h3 style="margin-bottom: 1rem; color: var(--primary);">
            Confirmación de Eliminación
        </h3>

        <p id="modal-texto-confirmacion">
            ¿Está seguro de realizar esta acción?
        </p>

        <div class="modal-acciones">

            <button
                type="button"
                class="btn btn-secondary"
                onclick="cerrarModal();"
            >
                Cancelar
            </button>

            <form
                action="<?php echo BASE_URL; ?>public/proveedores/eliminar"
                method="POST"
                style="display: inline;"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >

                <input
                    type="hidden"
                    name="id_proveedor"
                    id="id-proveedor-eliminar"
                    value=""
                >

                <button type="submit" class="btn btn-danger">
                    Eliminar
                </button>
            </form>

        </div>
    </div>
</div>

<script>
function prepararEliminacion(boton) {
    const id = boton.getAttribute('data-id');
    const nombre = boton.getAttribute('data-nombre');

    document.getElementById('id-proveedor-eliminar').value = id;

    document.getElementById('modal-texto-confirmacion').textContent =
        '¿Está seguro de eliminar al proveedor ' + nombre + '?';

    const modal = document.getElementById('modal-confirmacion');

    modal.style.display = 'flex';
    modal.classList.add('active');
}

function cerrarModal() {
    const modal = document.getElementById('modal-confirmacion');

    modal.style.display = 'none';
    modal.classList.remove('active');
}

</script>
