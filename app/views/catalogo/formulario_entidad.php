
<?php
$e = static function ($v) {
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
};
?>

<div style="margin-bottom:1rem">
    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>public/<?php echo $e($tipo); ?>">
        ⬅ Volver a <?php echo $e($nombre_tipo); ?>
    </a>
</div>

<div class="form-card" style="max-width:700px;margin:auto">

    <h2>
        <?php echo $id ? 'Editar ' : 'Registrar '; ?>
        <?php echo $e($nombre_tipo); ?>
    </h2>

    <?php if ($error !== ''): ?>

        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo $e($error); ?></div>
        </div>

    <?php endif; ?>

    <form
        method="POST"
        action="<?php echo BASE_URL; ?>public/<?php echo $e($tipo); ?>/guardar"
    >
        <input type="hidden" name="tipo" value="<?php echo $e($tipo); ?>">
        <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $e($csrf_token); ?>">

        <?php foreach ($config['campos'] as $f): ?>

            <div class="form-group">

                <label>
                    <?php echo $e($config['etiquetas'][$f]); ?>
                    <?php echo $f === 'nombre' ? ' (*)' : ''; ?>
                </label>

                <?php if ($f === 'descripcion'): ?>

                    <textarea
                        class="form-control"
                        name="<?php echo $e($f); ?>"
                        rows="4"
                    ><?php echo $e($registro[$f] ?? ''); ?></textarea>

                <?php else: ?>

                    <input
                        class="form-control"
                        type="text"
                        name="<?php echo $e($f); ?>"
                        maxlength="<?php echo $f === 'nacionalidad' ? 80 : ($f === 'contacto' ? 100 : 120); ?>"
                        value="<?php echo $e($registro[$f] ?? ''); ?>"
                        <?php echo $f === 'nombre' ? 'required' : ''; ?>
                    >

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

        <button class="btn btn-success btn-block" type="submit">
            Guardar
        </button>

    </form>
</div>
