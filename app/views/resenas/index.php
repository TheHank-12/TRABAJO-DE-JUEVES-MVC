
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>⭐ Moderación y Control de Reseñas</h2>
</div>

<div class="catalogo-header" style="margin-bottom: 1rem;">
    <div class="buscador-box">
        <input
            type="text"
            id="input-search-live"
            class="input-search"
            placeholder="Filtrar por libro, cliente o comentario..."
        >
    </div>
</div>

<div class="table-responsive">
    <table class="tabla-custom" id="tabla-resenas">
        <thead>
            <tr>
                <th>ID</th>
                <th>Libro</th>
                <th>Cliente</th>
                <th>Calificación</th>
                <th>Comentario</th>
                <th>Fecha</th>

                <?php if (es_admin()): ?>
                    <th>Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($resenas)): ?>

                <tr>
                    <td
                        colspan="<?php echo es_admin() ? '7' : '6'; ?>"
                        style="text-align: center;"
                    >
                        No hay reseñas registradas por los clientes aún.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($resenas as $r): ?>

                    <tr>
                        <td>
                            #<?php echo (int) $r['id_resena']; ?>
                        </td>

                        <td>
                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $r['libro_titulo'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </strong>

                            <br>

                            <small style="color: var(--text-muted);">
                                ISBN:
                                <?php
                                echo htmlspecialchars(
                                    $r['isbn'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </small>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                ($r['nombre'] ?? '') . ' ' . ($r['apellido'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>

                            <br>

                            <small style="color: var(--text-muted);">
                                <?php
                                echo htmlspecialchars(
                                    $r['email'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </small>
                        </td>

                        <td>
                            <?php
                            $puntuacion = max(0, min(5, (int) $r['puntuacion']));
                            ?>

                            <span style="color: #f59e0b; font-weight: bold;">
                                <?php
                                echo str_repeat('★', $puntuacion);
                                echo str_repeat('☆', 5 - $puntuacion);
                                ?>
                            </span>

                            <small>
                                (<?php echo $puntuacion; ?>/5)
                            </small>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $r['comentario'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            $fecha = $r['fecha_resena'] ?? ($r['created_at'] ?? null);

                            echo $fecha
                                ? date('d/m/Y H:i', strtotime($fecha))
                                : '-';
                            ?>
                        </td>

                        <?php if (es_admin()): ?>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm"
                                    data-id="<?php echo (int) $r['id_resena']; ?>"
                                    onclick="prepararEliminacionResena(this)"
                                >
                                    🗑️
                                </button>
                            </td>
                        <?php endif; ?>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (es_admin()): ?>

    <!-- Modal de confirmación -->
    <div class="modal-overlay" id="modal-confirmacion">

        <div class="modal-content">

            <h3 style="margin-bottom: 1rem; color: var(--primary);">
                Confirmación de Eliminación
            </h3>

            <p id="modal-texto-confirmacion">
                ¿Está seguro de eliminar esta reseña?
            </p>

            <form
                action="<?php echo BASE_URL; ?>public/resenas/eliminar"
                method="POST"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"
                >

                <input
                    type="hidden"
                    name="id_resena"
                    id="id-resena-eliminar"
                    value=""
                >

                <div class="modal-acciones">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="cerrarModalResena();"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        Eliminar
                    </button>

                </div>

            </form>

        </div>

    </div>

<?php endif; ?>

<script>
// Buscador en tiempo real
document.getElementById('input-search-live').addEventListener('input', function () {

    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll('#tabla-resenas tbody tr');

    filas.forEach(function (fila) {

        const contenido = fila.textContent.toLowerCase();

        fila.style.display = contenido.includes(filtro) ? '' : 'none';

    });

});

// Preparar eliminación
function prepararEliminacionResena(boton) {

    const id = boton.dataset.id;

    document.getElementById('id-resena-eliminar').value = id;

    const modal = document.getElementById('modal-confirmacion');

    modal.style.display = 'flex';
    modal.classList.add('active');
}

// Cerrar modal
function cerrarModalResena() {

    const modal = document.getElementById('modal-confirmacion');

    modal.style.display = 'none';
    modal.classList.remove('active');

    document.getElementById('id-resena-eliminar').value = '';

}
</script>
