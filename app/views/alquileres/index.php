<?php $titulo_pagina = "Gestión de Alquileres"; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>📖 Gestión de Alquileres de Libros</h2>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="<?php echo BASE_URL; ?>alquileres/multas" class="btn btn-warning">⚠️ Control de Multas</a>
        <a href="<?php echo BASE_URL; ?>alquileres/crear" class="btn btn-success">➕ Nuevo Alquiler</a>
    </div>
</div>

<div class="catalogo-header" style="margin-bottom: 1rem;">
    <div class="buscador-box">
        <input type="text" id="input-search-live" class="input-search" placeholder="Filtrar por cliente, DNI o libro..." value="<?php echo htmlspecialchars($busqueda ?? ''); ?>">
    </div>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente / DNI</th>
                <th>Libro</th>
                <th>Fecha Inicio</th>
                <th>Dev. Esperada</th>
                <th>Dev. Real</th>
                <th>Monto S/</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alquileres)): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">No hay alquileres registrados en el sistema.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($alquileres as $a): ?>
                    <tr>
                        <td>#<?php echo (int)$a['id_alquiler']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($a['usuario_nombre'] . ' ' . $a['usuario_apellido']); ?></strong><br>
                            <small style="color: var(--text-muted);">DNI: <?php echo htmlspecialchars($a['usuario_dni']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($a['libro_titulo']); ?></strong><br>
                            <small style="color: var(--text-muted);">ISBN: <?php echo htmlspecialchars($a['libro_isbn']); ?></small>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($a['fecha_inicio'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($a['fecha_dev_esperada'])); ?></td>
                        <td><?php echo !empty($a['fecha_dev_real']) ? date('d/m/Y', strtotime($a['fecha_dev_real'])) : '-'; ?></td>
                        <td><strong>S/ <?php echo number_format((float)$a['monto_alquiler'], 2); ?></strong></td>
                        <td>
                            <?php if ($a['estado'] === 'En Alquiler'): ?>
                                <span style="background-color: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">EN ALQUILER</span>
                            <?php else: ?>
                                <span style="background-color: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;"><?php echo htmlspecialchars(strtoupper($a['estado'])); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                <?php if ($a['estado'] === 'En Alquiler'): ?>
                                    <a href="<?php echo BASE_URL; ?>alquileres/devolver?id=<?php echo (int)$a['id_alquiler']; ?>" class="btn btn-primary btn-sm">📥 Devolver</a>
                                <?php endif; ?>
                                <?php if (!empty($a['id_multa'])): ?>
                                    <a href="<?php echo BASE_URL; ?>alquileres/multas" class="btn btn-danger btn-sm" title="Ver Multa (S/ <?php echo number_format((float)$a['monto_multa'], 2); ?>)">⚠️ Multa</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
