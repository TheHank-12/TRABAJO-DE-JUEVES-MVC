<?php include_once ROOT_PATH . '/app/views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>⭐ Moderación y Control de Reseñas</h2>
</div>

<div class="catalogo-header" style="margin-bottom: 1rem;">
    <div class="buscador-box">
        <input type="text" id="input-search-live" class="input-search" placeholder="Filtrar por libro, cliente o comentario...">
    </div>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
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
                    <td colspan="<?php echo es_admin() ? '7' : '6'; ?>" style="text-align: center;">No hay reseñas registradas por los clientes aún.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($resenas as $r): ?>
                    <tr>
                        <td>#<?php echo $r['id_resena']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($r['libro_titulo']); ?></strong><br>
                            <small style="color: var(--text-muted);">ISBN: <?php echo htmlspecialchars($r['isbn']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($r['nombre'] . ' ' . $r['apellido']); ?><br>
                            <small style="color: var(--text-muted);"><?php echo htmlspecialchars($r['email']); ?></small>
                        </td>
                        <td>
                            <span style="color: #f59e0b; font-weight: bold;">
                                <?php echo str_repeat('★', (int)$r['puntuacion']) . str_repeat('☆', 5 - (int)$r['puntuacion']); ?>
                            </span>
                            <small>(<?php echo (int)$r['puntuacion']; ?>/5)</small>
                        </td>
                        <td><?php echo htmlspecialchars($r['comentario']); ?></td>
                        <td>
                            <?php
                            $fecha = $r['fecha_resena'] ?? ($r['created_at'] ?? null);
                            echo $fecha ? date('d/m/Y H:i', strtotime($fecha)) : '-';
                            ?>
                        </td>
                        <?php if (es_admin()): ?>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmarAccion('<?php echo BASE_URL; ?>public/resenas/eliminar?id=<?php echo $r['id_resena']; ?>', '¿Está seguro de eliminar esta reseña?');">🗑️</button>
                            </td>
                        <?php endif; ?>
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