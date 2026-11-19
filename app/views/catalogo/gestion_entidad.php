
<?php
$csrf_token = $_SESSION['csrf_catalogo'] ?? '';

$e = static function ($v) {
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
};
?>

<div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">

    <h2>Administración de <?php echo $e($nombre_tipo); ?></h2>

    <div style="display:flex;gap:8px;flex-wrap:wrap;">

        <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>public/libros">
            ⬅ Gestión de libros
        </a>

        <a class="btn btn-success" href="<?php echo BASE_URL; ?>public/<?php echo $e($tipo); ?>/crear">
            ➕ Nuevo registro
        </a>

    </div>
</div>

<form
    method="GET"
    action="<?php echo BASE_URL; ?>public/<?php echo $e($tipo); ?>"
    style="display:flex;gap:8px;margin-bottom:1rem;"
>
    <input
        class="input-search"
        type="search"
        name="q"
        value="<?php echo $e($busqueda); ?>"
        placeholder="Buscar <?php echo strtolower($e($nombre_tipo)); ?>"
    >

    <button class="btn btn-primary">Buscar</button>
</form>

<div class="table-responsive">

    <table class="tabla-custom">

        <thead>
            <tr>
                <th>ID</th>

                <?php foreach ($config['campos'] as $f): ?>
                    <th><?php echo $e($config['etiquetas'][$f]); ?></th>
                <?php endforeach; ?>

                <th>Libros asociados</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>

            <?php if (!$registros): ?>

                <tr>
                    <td colspan="<?php echo count($config['campos']) + 3; ?>" style="text-align:center">
                        No hay registros.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($registros as $r): ?>

                    <tr>
                        <td><?php echo (int) $r[$config['pk']]; ?></td>

                        <?php foreach ($config['campos'] as $f): ?>
                            <td><?php echo $e($r[$f] ?? '—'); ?></td>
                        <?php endforeach; ?>

                        <td><?php echo (int) $r['total_libros']; ?></td>

                        <td>
                            <div style="display:flex;gap:5px">

                                <a
                                    class="btn btn-primary btn-sm"
                                    href="<?php echo BASE_URL; ?>public/<?php echo $e($tipo); ?>/editar?id=<?php echo (int) $r[$config['pk']]; ?>"
                                >
                                    Editar
                                </a>

                                <?php if (es_admin()): ?>

                                    <form
                                        method="POST"
                                        action="<?php echo BASE_URL; ?>public/<?php echo $e($tipo); ?>/eliminar"
                                        onsubmit="return confirm('¿Eliminar este registro?');"
                                    >
                                        <input type="hidden" name="tipo" value="<?php echo $e($tipo); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int) $r[$config['pk']]; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $e($csrf_token); ?>">

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
