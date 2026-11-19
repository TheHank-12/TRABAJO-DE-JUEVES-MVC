
<?php
$csrf_token = (string) ($csrf_token ?? '');
?>

<div style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;align-items:center;margin-bottom:1.5rem;">
    <h2>📚 Gestión del Catálogo de Libros</h2>

    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a class="btn btn-secondary btn-sm" href="<?php echo BASE_URL; ?>public/categorias">🏷️ Categorías</a>
        <a class="btn btn-secondary btn-sm" href="<?php echo BASE_URL; ?>public/autores">✍️ Autores</a>
        <a class="btn btn-secondary btn-sm" href="<?php echo BASE_URL; ?>public/editoriales">🏢 Editoriales</a>
        <a class="btn btn-success" href="<?php echo BASE_URL; ?>public/libros/crear">➕ Añadir libro</a>
    </div>
</div>

<form method="GET" action="<?php echo BASE_URL; ?>public/libros" style="display:flex;gap:8px;margin-bottom:1rem;">
    <input
        class="input-search"
        type="search"
        name="q"
        placeholder="Buscar por título, ISBN, autor o categoría"
        value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>"
    >
    <button class="btn btn-primary">Buscar</button>
</form>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>Portada</th>
                <th>Título / ISBN</th>
                <th>Autor</th>
                <th>Categoría</th>
                <th>Editorial</th>
                <th>Venta</th>
                <th>Alquiler</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!$libros): ?>
                <tr>
                    <td colspan="10" style="text-align:center;">
                        No hay libros que coincidan con la búsqueda.
                    </td>
                </tr>
            <?php else: ?>

                <?php foreach ($libros as $l): ?>
                    <tr>
                        <td>
                            <img
                                src="<?php echo BASE_URL . 'public/' . htmlspecialchars($l['portada'] ?: 'assets/img/no-cover.png.jpg', ENT_QUOTES, 'UTF-8'); ?>"
                                alt="Portada"
                                style="width:45px;height:60px;object-fit:cover"
                                onerror="this.src='<?php echo BASE_URL; ?>public/assets/img/no-cover.png.jpg'"
                            >
                        </td>

                        <td>
                            <strong><?php echo htmlspecialchars($l['titulo'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <br>
                            <small>
                                ISBN:
                                <?php echo htmlspecialchars($l['isbn'], ENT_QUOTES, 'UTF-8'); ?>
                            </small>
                        </td>

                        <td><?php echo htmlspecialchars($l['autor'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($l['categoria'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($l['editorial'], ENT_QUOTES, 'UTF-8'); ?></td>

                        <td>S/ <?php echo number_format($l['precio_venta'], 2); ?></td>
                        <td>S/ <?php echo number_format($l['precio_alquiler'], 2); ?></td>
                        <td><?php echo (int) $l['stock']; ?></td>

                        <td><?php echo htmlspecialchars($l['estado'], ENT_QUOTES, 'UTF-8'); ?></td>

                        <td>
                            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                                <a
                                    class="btn btn-secondary btn-sm"
                                    href="<?php echo BASE_URL; ?>public/libros/detalle?id=<?php echo (int) $l['id_libro']; ?>"
                                >Ver</a>

                                <a
                                    class="btn btn-primary btn-sm"
                                    href="<?php echo BASE_URL; ?>public/libros/editar?id=<?php echo (int) $l['id_libro']; ?>"
                                >Editar</a>

                                <?php if (es_admin()): ?>
                                    <form
                                        method="POST"
                                        action="<?php echo BASE_URL; ?>public/libros/eliminar"
                                        onsubmit="return confirm('¿Eliminar este libro?');"
                                    >
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="id_libro" value="<?php echo (int) $l['id_libro']; ?>">

                                        <button class="btn btn-danger btn-sm">
                                            Eliminar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php endif; ?>
        </tbody>
    </table>
</div>
