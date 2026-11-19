
<?php if (empty($libro)): ?>

    <div class="alerta-box alerta-info">
        <div class="alerta-contenido">
            El libro solicitado no existe.
        </div>
    </div>

    <a class="btn btn-primary" href="<?php echo BASE_URL; ?>public/">
        Volver al catálogo
    </a>

<?php else: ?>

    <?php
    $e = static function ($v) {
        return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
    };
    ?>

    <div style="margin-bottom:1rem">
        <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>public/">
            ⬅ Volver al catálogo
        </a>
    </div>

    <div class="form-card" style="display:grid;grid-template-columns:minmax(180px,280px) 1fr;gap:2rem;align-items:start;">

        <img
            src="<?php echo BASE_URL . 'public/' . $e($libro['portada'] ?: 'assets/img/no-cover.png.jpg'); ?>"
            alt="Portada"
            style="width:100%;max-height:390px;object-fit:contain"
            onerror="this.src='<?php echo BASE_URL; ?>public/assets/img/no-cover.png.jpg'"
        >

        <div>
            <h2><?php echo $e($libro['titulo']); ?></h2>

            <p>
                <strong>Autor:</strong>
                <?php echo $e($libro['autor']); ?>

                <?php if (!empty($libro['autor_nacionalidad'])): ?>
                    (<?php echo $e($libro['autor_nacionalidad']); ?>)
                <?php endif; ?>
            </p>

            <p><strong>Categoría:</strong> <?php echo $e($libro['categoria']); ?></p>
            <p><strong>Editorial:</strong> <?php echo $e($libro['editorial']); ?></p>
            <p><strong>ISBN:</strong> <?php echo $e($libro['isbn']); ?></p>

            <p>
                <strong>Publicación:</strong>
                <?php echo $e($libro['fecha_publicacion'] ?: 'No especificada'); ?>
            </p>

            <p>
                <strong>Disponibilidad:</strong>
                <?php echo $e($libro['estado']); ?>
                (<?php echo (int) $libro['stock']; ?> unidades)
            </p>

            <h3>
                Venta: S/ <?php echo number_format($libro['precio_venta'], 2); ?>
            </h3>

            <p>
                Alquiler por día:
                S/ <?php echo number_format($libro['precio_alquiler'], 2); ?>
            </p>

            <?php if ((int) $libro['stock'] > 0): ?>

                <div style="display:flex;gap:8px;flex-wrap:wrap;margin:1rem 0;">

                    <form action="<?php echo BASE_URL; ?>public/carrito/agregar" method="POST">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo htmlspecialchars(generar_token_csrf(), ENT_QUOTES, 'UTF-8'); ?>"
                        >

                        <input
                            type="hidden"
                            name="id_libro"
                            value="<?php echo (int)$libro['id_libro']; ?>"
                        >

                        <input type="hidden" name="cantidad" value="1">

                        <button type="submit" class="btn btn-primary">
                            🛒 Añadir al carrito
                        </button>

                    </form>

                    <a
                        class="btn btn-success"
                        href="<?php echo BASE_URL; ?>public/solicitar-alquiler?id_libro=<?php echo (int) $libro['id_libro']; ?>"
                    >
                        📖 Solicitar alquiler
                    </a>

                </div>

            <?php else: ?>

                <p><strong>Agotado temporalmente.</strong></p>

            <?php endif; ?>

            <p>
                Valoración:
                <?php echo number_format($libro['promedio_estrellas'], 1); ?>/5
                (<?php echo (int) $libro['total_reseñas']; ?> reseñas)
            </p>
        </div>
    </div>

    <section style="margin-top:2rem">

        <h2>Reseñas de clientes</h2>

        <?php if (esta_autenticado()): ?>

            <div class="form-card" style="margin-bottom:1rem">
                <h3>Dejar una valoración</h3>

                <form method="POST" action="<?php echo BASE_URL; ?>public/libros/resena">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >

                    <input
                        type="hidden"
                        name="id_libro"
                        value="<?php echo (int) $libro['id_libro']; ?>"
                    >

                    <div class="form-group">
                        <label>Puntuación:</label>

                        <select class="form-control" name="puntuacion">
                            <option value="5">5 - Excelente</option>
                            <option value="4">4 - Muy bueno</option>
                            <option value="3">3 - Bueno</option>
                            <option value="2">2 - Regular</option>
                            <option value="1">1 - Bajo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Comentario:</label>
                        <textarea class="form-control" name="comentario" rows="3" required></textarea>
                    </div>

                    <button class="btn btn-primary" type="submit">
                        Publicar reseña
                    </button>
                </form>
            </div>

        <?php else: ?>

            <p>
                <a href="<?php echo BASE_URL; ?>public/login">Inicie sesión</a>
                para dejar una reseña.
            </p>

        <?php endif; ?>

        <?php if (!$resenas): ?>

            <p>Aún no hay reseñas para este libro.</p>

        <?php else: ?>

            <?php foreach ($resenas as $r): ?>

                <div class="form-card" style="margin-bottom:1rem">

                    <strong>
                        <?php echo $e($r['nombre'] . ' ' . $r['apellido']); ?>
                    </strong>

                    <div style="color:#d97706">
                        <?php
                        echo str_repeat('★', (int) $r['puntuacion'])
                            . str_repeat('☆', 5 - (int) $r['puntuacion']);
                        ?>
                    </div>

                    <p><?php echo nl2br($e($r['comentario'])); ?></p>

                    <small><?php echo $e($r['fecha']); ?></small>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

<?php endif; ?>
