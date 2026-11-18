
<?php
// Los datos llegan desde LibroController.php:
// $categorias, $editoriales, $librosTop, $libros,
// $categoria_id, $editorial_id, $orden y $busqueda.

$totalLibros = count($libros);
?>

<!-- 1. BANNER DE BIENVENIDA -->
<div style="background-color: var(--secondary); color: #fff; padding: 2rem; border-radius: var(--radius); margin-bottom: 2rem; text-align: center;">
    <h1 style="color: var(--accent); margin-bottom: 0.5rem;">Bienvenido a la Librería Vallejo</h1>
    <p style="font-size: 1.1rem; max-width: 800px; margin: 0 auto;">
        Explore nuestro amplio catálogo de obras literarias, novedades académicas y clásicos. Disponibles para venta y alquiler a nivel nacional.
    </p>
</div>

<!-- 2. LIBROS MEJOR VALORADOS -->
<?php if (!empty($librosTop)): ?>
    <div style="margin-bottom: 2.5rem; background: #fff8f0; padding: 1.5rem; border-radius: var(--radius); border: 1px solid #ffe8d6;">
        <h2 style="color: #d97706; margin-top: 0; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
            ⭐ Libros Mejor Valorados por la Comunidad
        </h2>

        <div class="grid-libros">
            <?php foreach ($librosTop as $top): ?>
                <div class="card-libro" style="border: 2px solid #fef3c7;">
                    <div class="card-portada-wrapper">
                        <img
                            src="<?php echo BASE_URL . 'public/' . htmlspecialchars($top['portada']); ?>"
                            alt="Portada de <?php echo htmlspecialchars($top['titulo']); ?>"
                            class="card-portada"
                            onerror="this.src='<?php echo BASE_URL; ?>public/assets/img/no-cover.png.jpg'"
                        >
                        <span class="card-badge-stock" style="background: #d97706;">TOP ★</span>
                    </div>

                    <div class="card-body">
                        <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #d97706; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($top['categoria']); ?>
                        </span>

                        <h3 class="card-titulo">
                            <?php echo htmlspecialchars($top['titulo']); ?>
                        </h3>

                        <p class="card-autor">
                            Por: <?php echo htmlspecialchars($top['autor']); ?>
                        </p>

                        <div class="card-estrellas" style="color: #f59e0b;">
                            <?php
                            $prom_top = round($top['promedio_estrellas']);

                            for ($i = 1; $i <= 5; $i++) {
                                echo ($i <= $prom_top) ? '★' : '☆';
                            }
                            ?>

                            <span style="font-size: 0.8rem; color: var(--text-muted);">
                                (<?php echo $top['total_reseñas']; ?>)
                            </span>
                        </div>

                        <div class="card-precios">
                            <div>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display:block;">Venta</span>
                                <span class="precio-venta">
                                    S/ <?php echo number_format($top['precio_venta'], 2); ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-acciones" style="margin-top: 0.8rem;">
                            <a
                                href="<?php echo BASE_URL; ?>modulos/libros/detalle.php?id=<?php echo (int)$top['id_libro']; ?>"
                                class="btn btn-primary btn-sm btn-block"
                            >
                                👁️ Ver Detalle
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ANCLA DEL CATÁLOGO -->
<div id="catalogo"></div>

<!-- 3. FILTROS Y BÚSQUEDA -->
<form
    action="<?php echo BASE_URL; ?>"
    method="GET"
    style="background: #ffffff; padding: 1.2rem; border-radius: var(--radius); border: 1px solid var(--border-color); margin-bottom: 1.5rem;"
>
    <?php if ($categoria_id > 0): ?>
        <input type="hidden" name="categoria" value="<?php echo (int)$categoria_id; ?>">
    <?php endif; ?>

    <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; justify-content: space-between;">

        <!-- Búsqueda -->
        <div style="flex: 2; min-width: 220px;">
            <input
                type="text"
                name="q"
                class="input-search"
                placeholder="Buscar por título, autor o ISBN..."
                value="<?php echo htmlspecialchars($busqueda); ?>"
                style="width: 100%;"
            >
        </div>

        <!-- Editorial -->
        <div style="flex: 1; min-width: 180px;">
            <select name="editorial" class="form-control" onchange="this.form.submit()">
                <option value="0">-- Todas las Editoriales --</option>

                <?php foreach ($editoriales as $ed): ?>
                    <option
                        value="<?php echo (int)$ed['id_editorial']; ?>"
                        <?php echo $editorial_id === (int)$ed['id_editorial'] ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($ed['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Ordenamiento -->
        <div style="flex: 1; min-width: 180px;">
            <select name="orden" class="form-control" onchange="this.form.submit()">
                <option value="recientes" <?php echo $orden === 'recientes' ? 'selected' : ''; ?>>🆕 Más recientes</option>
                <option value="precio_asc" <?php echo $orden === 'precio_asc' ? 'selected' : ''; ?>>💲 Precio: Menor a Mayor</option>
                <option value="precio_desc" <?php echo $orden === 'precio_desc' ? 'selected' : ''; ?>>💲 Precio: Mayor a Menor</option>
                <option value="rating" <?php echo $orden === 'rating' ? 'selected' : ''; ?>>⭐ Mejor valorados</option>
                <option value="titulo_asc" <?php echo $orden === 'titulo_asc' ? 'selected' : ''; ?>>🔤 Título (A - Z)</option>
            </select>
        </div>

        <!-- Botones -->
        <div style="display: flex; gap: 8px; align-items: center;">
            <button type="submit" class="btn btn-primary">🔍 Buscar</button>

            <?php if (!empty($busqueda) || $categoria_id > 0 || $editorial_id > 0 || $orden !== 'recientes'): ?>
                <a
                    href="<?php echo BASE_URL; ?>#catalogo"
                    class="btn btn-danger"
                    title="Limpiar todos los filtros"
                >
                    ✖ Limpiar
                </a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- 4. FILTROS POR CATEGORÍA -->
<div style="margin-bottom: 2rem; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
    <span style="font-weight: 600; font-size: 0.9rem; margin-right: 5px;">
        Categoría:
    </span>

    <a
        href="<?php echo BASE_URL; ?>?editorial=<?php echo $editorial_id; ?>&orden=<?php echo urlencode($orden); ?>&q=<?php echo urlencode($busqueda); ?>#catalogo"
        class="btn btn-sm <?php echo $categoria_id === 0 ? 'btn-primary' : 'btn-secondary'; ?>"
    >
        Todas
    </a>

    <?php foreach ($categorias as $cat): ?>
        <a
            href="<?php echo BASE_URL; ?>?categoria=<?php echo (int)$cat['id_categoria']; ?>&editorial=<?php echo $editorial_id; ?>&orden=<?php echo urlencode($orden); ?>&q=<?php echo urlencode($busqueda); ?>#catalogo"
            class="btn btn-sm <?php echo $categoria_id === (int)$cat['id_categoria'] ? 'btn-primary' : 'btn-secondary'; ?>"
        >
            <?php echo htmlspecialchars($cat['nombre']); ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- 5. CATÁLOGO GENERAL -->
<div class="catalogo-header">
    <h2>Catálogo de Libros (<?php echo $totalLibros; ?> disponibles)</h2>
</div>

<?php if ($totalLibros === 0): ?>

    <div class="alerta-box alerta-info">
        <div class="alerta-icono">ℹ️</div>
        <div class="alerta-contenido">
            No se encontraron libros que coincidan con los criterios de búsqueda o filtros especificados.
        </div>
    </div>

<?php else: ?>

    <div class="grid-libros">
        <?php foreach ($libros as $libro): ?>

            <div class="card-libro">
                <div class="card-portada-wrapper">
                    <img
                        src="<?php echo BASE_URL . 'public/' . htmlspecialchars($libro['portada']); ?>"
                        alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>"
                        class="card-portada"
                        onerror="this.src='<?php echo BASE_URL; ?>public/assets/img/no-cover.png.jpg'"
                    >

                    <span class="card-badge-stock">
                        Stock: <?php echo (int)$libro['stock']; ?>
                    </span>
                </div>

                <div class="card-body">
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--primary); margin-bottom: 4px;">
                        <?php echo htmlspecialchars($libro['categoria']); ?>
                    </span>

                    <h3 class="card-titulo">
                        <?php echo htmlspecialchars($libro['titulo']); ?>
                    </h3>

                    <p class="card-autor">
                        Por: <?php echo htmlspecialchars($libro['autor']); ?>
                    </p>

                    <div class="card-estrellas">
                        <?php
                        $prom = round($libro['promedio_estrellas']);

                        for ($i = 1; $i <= 5; $i++) {
                            echo ($i <= $prom) ? '★' : '☆';
                        }
                        ?>

                        <span style="font-size: 0.8rem; color: var(--text-muted);">
                            (<?php echo $libro['total_reseñas']; ?>)
                        </span>
                    </div>

                    <div class="card-precios">
                        <div>
                            <span style="font-size: 0.75rem; color: var(--text-muted); display:block;">Venta</span>
                            <span class="precio-venta">
                                S/ <?php echo number_format($libro['precio_venta'], 2); ?>
                            </span>
                        </div>

                        <div style="text-align: right;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); display:block;">Alquiler/Día</span>
                            <span class="precio-alquiler">
                                S/ <?php echo number_format($libro['precio_alquiler'], 2); ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-acciones">
                        <a
                            href="<?php echo BASE_URL; ?>modulos/libros/detalle.php?id=<?php echo (int)$libro['id_libro']; ?>"
                            class="btn btn-secondary btn-sm"
                            style="flex: 1;"
                        >
                            👁️ Detalle
                        </a>

                        <?php if ($libro['stock'] > 0): ?>
                            <form
                                action="<?php echo BASE_URL; ?>modulos/carrito/agregar.php"
                                method="POST"
                                style="flex: 1;"
                            >
                                <input type="hidden" name="id_libro" value="<?php echo (int)$libro['id_libro']; ?>">
                                <input type="hidden" name="cantidad" value="1">

                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    🛒 Añadir
                                </button>
                            </form>
                        <?php else: ?>
                            <button
                                class="btn btn-sm"
                                style="flex: 1; background-color: #ccc; cursor: not-allowed;"
                                disabled
                            >
                                Agotado
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>

<?php endif; ?>
