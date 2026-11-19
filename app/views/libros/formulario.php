
<?php
$e = static function ($v) {
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
};
?>

<div style="margin-bottom:1rem">
    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>public/libros">
        ⬅ Volver a libros
    </a>
</div>

<div class="form-card" style="max-width:900px;margin:auto">
    <h2><?php echo $id ? 'Editar libro' : 'Registrar nuevo libro'; ?></h2>

    <?php if ($error !== ''): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo $e($error); ?></div>
        </div>
    <?php endif; ?>

    <form
        method="POST"
        action="<?php echo BASE_URL; ?>public/libros/guardar"
        enctype="multipart/form-data"
    >
        <input type="hidden" name="csrf_token" value="<?php echo $e($csrf_token); ?>">
        <input type="hidden" name="id_libro" value="<?php echo (int) $id; ?>">
        <input type="hidden" name="portada_actual" value="<?php echo $e($datos['portada'] ?? ''); ?>">

        <div class="form-group">
            <label>Título (*):</label>
            <input
                class="form-control"
                name="titulo"
                maxlength="200"
                required
                value="<?php echo $e($datos['titulo'] ?? ''); ?>"
            >
        </div>

        <div class="form-group">
            <label>ISBN (*):</label>
            <input
                class="form-control"
                name="isbn"
                maxlength="30"
                required
                value="<?php echo $e($datos['isbn'] ?? ''); ?>"
            >
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1rem;">

            <div class="form-group">
                <label>Categoría (*):</label>
                <select class="form-control" name="id_categoria" required>
                    <option value="">Seleccione</option>

                    <?php foreach ($categorias as $x): ?>
                        <option
                            value="<?php echo (int) $x['id_categoria']; ?>"
                            <?php echo (int) ($datos['id_categoria'] ?? 0) === (int) $x['id_categoria'] ? 'selected' : ''; ?>
                        >
                            <?php echo $e($x['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Autor (*):</label>
                <select class="form-control" name="id_autor" required>
                    <option value="">Seleccione</option>

                    <?php foreach ($autores as $x): ?>
                        <option
                            value="<?php echo (int) $x['id_autor']; ?>"
                            <?php echo (int) ($datos['id_autor'] ?? 0) === (int) $x['id_autor'] ? 'selected' : ''; ?>
                        >
                            <?php echo $e($x['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Editorial (*):</label>
                <select class="form-control" name="id_editorial" required>
                    <option value="">Seleccione</option>

                    <?php foreach ($editoriales as $x): ?>
                        <option
                            value="<?php echo (int) $x['id_editorial']; ?>"
                            <?php echo (int) ($datos['id_editorial'] ?? 0) === (int) $x['id_editorial'] ? 'selected' : ''; ?>
                        >
                            <?php echo $e($x['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Proveedor (*):</label>
                <select class="form-control" name="id_proveedor" required>
                    <option value="">Seleccione</option>

                    <?php foreach ($proveedores as $x): ?>
                        <option
                            value="<?php echo (int) $x['id_proveedor']; ?>"
                            <?php echo (int) ($datos['id_proveedor'] ?? 0) === (int) $x['id_proveedor'] ? 'selected' : ''; ?>
                        >
                            <?php echo $e($x['razon_social']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;">

            <div class="form-group">
                <label>Precio de venta (S/):</label>
                <input
                    class="form-control"
                    type="number"
                    name="precio_venta"
                    min="0"
                    step="0.01"
                    required
                    value="<?php echo $e($datos['precio_venta'] ?? '0'); ?>"
                >
            </div>

            <div class="form-group">
                <label>Precio de alquiler (S/):</label>
                <input
                    class="form-control"
                    type="number"
                    name="precio_alquiler"
                    min="0"
                    step="0.01"
                    required
                    value="<?php echo $e($datos['precio_alquiler'] ?? '0'); ?>"
                >
            </div>

            <div class="form-group">
                <label>Stock:</label>
                <input
                    class="form-control"
                    type="number"
                    name="stock"
                    min="0"
                    required
                    value="<?php echo $e($datos['stock'] ?? 0); ?>"
                >
            </div>

            <div class="form-group">
                <label>Fecha de publicación:</label>
                <input
                    class="form-control"
                    type="date"
                    name="fecha_publicacion"
                    value="<?php echo $e($datos['fecha_publicacion'] ?? ''); ?>"
                >
            </div>

            <div class="form-group">
                <label>Estado:</label>
                <select class="form-control" name="estado">
                    <option value="DISPONIBLE" <?php echo ($datos['estado'] ?? '') === 'DISPONIBLE' ? 'selected' : ''; ?>>
                        DISPONIBLE
                    </option>
                    <option value="AGOTADO" <?php echo ($datos['estado'] ?? '') === 'AGOTADO' ? 'selected' : ''; ?>>
                        AGOTADO
                    </option>
                    <option value="DESCONTINUADO" <?php echo ($datos['estado'] ?? '') === 'DESCONTINUADO' ? 'selected' : ''; ?>>
                        DESCONTINUADO
                    </option>
                </select>
            </div>

        </div>

        <div class="form-group">
            <label>Portada (JPG, PNG o WEBP; máximo 3 MB):</label>

            <input
                class="form-control"
                type="file"
                name="portada"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            >

            <?php if (!empty($datos['portada'])): ?>
                <small>Portada actual: <?php echo $e($datos['portada']); ?></small>
            <?php endif; ?>
        </div>

        <button class="btn btn-success btn-block" type="submit">
            Guardar libro
        </button>
    </form>
</div>
