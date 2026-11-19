
<?php
$escapar = function ($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
};
?>

<div style="margin-bottom: 1.5rem;">
    <h2>🛒 Carrito de Compras</h2>
    <p style="color: var(--text-muted);">
        Revise los libros seleccionados antes de finalizar su compra.
    </p>
</div>

<?php if (empty($items)): ?>

    <div class="form-card" style="text-align:center; padding:3rem;">
        <div style="font-size:3rem; margin-bottom:1rem;">🛒</div>

        <h3>Su carrito está vacío</h3>

        <p style="color:var(--text-muted); margin-bottom:1.5rem;">
            Explore nuestro catálogo para agregar libros.
        </p>

        <a href="<?php echo BASE_URL; ?>public/catalogo"
           class="btn btn-primary">
            📚 Explorar catálogo
        </a>
    </div>

<?php else: ?>

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; align-items:start;">

        <!-- Productos -->
        <div>

            <div class="table-responsive"
                 style="background:#fff; border-radius:var(--radius); border:1px solid var(--border-color);">

                <table class="tabla-custom">
                    <thead>
                        <tr>
                            <th>Portada</th>
                            <th>Libro</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Importe</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($items as $item): ?>

                            <tr>
                                <td style="text-align:center;">
                                    <img
                                        src="<?php echo BASE_URL . 'public/' . $escapar($item['portada']); ?>"
                                        alt="Portada del libro"
                                        style="width:45px;height:60px;object-fit:cover;border-radius:4px;"
                                        onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>public/assets/img/no-cover.png.jpg';"
                                    >
                                </td>

                                <td>
                                    <strong>
                                        <?php echo $escapar($item['titulo']); ?>
                                    </strong>
                                    <br>
                                    <small>
                                        ISBN: <?php echo $escapar($item['isbn']); ?>
                                    </small>
                                </td>

                                <td>
                                    S/ <?php echo number_format($item['precio'], 2); ?>
                                </td>

                                <td>
                                    <form
                                        action="<?php echo BASE_URL; ?>public/carrito/actualizar"
                                        method="POST"
                                        style="display:flex;gap:5px;align-items:center;"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php echo $escapar($csrf_token); ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id_libro"
                                            value="<?php echo (int)$item['id_libro']; ?>"
                                        >

                                        <input
                                            type="number"
                                            name="cantidad"
                                            value="<?php echo (int)$item['cantidad']; ?>"
                                            min="1"
                                            max="<?php echo (int)$item['stock']; ?>"
                                            required
                                            style="width:65px;"
                                        >

                                        <button type="submit"
                                                class="btn btn-secondary btn-sm"
                                                title="Actualizar cantidad">
                                            🔄
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    <strong>
                                        S/ <?php echo number_format($item['subtotal'], 2); ?>
                                    </strong>
                                </td>

                                <td>
                                    <form
                                        action="<?php echo BASE_URL; ?>public/carrito/eliminar"
                                        method="POST"
                                        onsubmit="return confirm('¿Desea quitar este libro del carrito?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php echo $escapar($csrf_token); ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id_libro"
                                            value="<?php echo (int)$item['id_libro']; ?>"
                                        >

                                        <button type="submit"
                                                class="btn btn-danger btn-sm">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

            <div style="display:flex;justify-content:space-between;gap:10px;margin-top:1rem;">

                <a href="<?php echo BASE_URL; ?>public/catalogo"
                   class="btn btn-secondary">
                    ⬅️ Seguir comprando
                </a>

                <form
                    action="<?php echo BASE_URL; ?>public/carrito/vaciar"
                    method="POST"
                    onsubmit="return confirm('¿Desea vaciar todo el carrito?');"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo $escapar($csrf_token); ?>"
                    >

                    <button type="submit" class="btn btn-danger btn-sm">
                        🗑️ Vaciar carrito
                    </button>
                </form>

            </div>

        </div>

        <!-- Resumen -->
        <div class="form-card" style="max-width:100%;padding:1.5rem;">

            <h3 style="margin-bottom:1rem;">
                Resumen de compra
            </h3>

            <div style="display:flex;justify-content:space-between;margin-bottom:.7rem;">
                <span>Subtotal:</span>
                <strong>S/ <?php echo number_format($subtotal, 2); ?></strong>
            </div>

            <div style="display:flex;justify-content:space-between;margin-bottom:.7rem;">
                <span>IGV (18%):</span>
                <strong>S/ <?php echo number_format($igv, 2); ?></strong>
            </div>

            <div style="display:flex;justify-content:space-between;font-size:1.2rem;border-top:1px solid var(--border-color);padding-top:1rem;margin-bottom:1rem;">
                <strong>Total:</strong>
                <strong>S/ <?php echo number_format($total, 2); ?></strong>
            </div>

            <?php if (esta_autenticado()): ?>

                <?php if (!es_personal() || es_admin()): ?>

                    <form
                        action="<?php echo BASE_URL; ?>public/carrito/procesar"
                        method="POST"
                        onsubmit="return confirm('¿Confirma que desea registrar esta compra?');"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo $escapar($csrf_token); ?>"
                        >

                        <?php if (es_admin()): ?>

                            <div class="form-group">
                                <label for="id_usuario_cliente">
                                    Cliente de la venta:
                                </label>

                                <select
                                    name="id_usuario_cliente"
                                    id="id_usuario_cliente"
                                    class="form-control"
                                    required
                                >
                                    <option value="">Seleccionar cliente</option>

                                    <?php foreach ($clientes as $cliente): ?>

                                        <option value="<?php echo (int)$cliente['id_usuario']; ?>">
                                            <?php echo $escapar(
                                                $cliente['nombre'] . ' ' .
                                                $cliente['apellido'] .
                                                ' - DNI: ' . $cliente['dni']
                                            ); ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>
                            </div>

                        <?php endif; ?>

                        <div class="form-group">
                            <label for="metodo_pago">Método de pago:</label>

                            <select
                                name="metodo_pago"
                                id="metodo_pago"
                                class="form-control"
                                required
                            >
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="YAPE">Yape</option>
                                <option value="PLIN">Plin</option>
                                <option value="TARJETA">Tarjeta</option>
                                <option value="TRANSFERENCIA">Transferencia</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="num_operacion">
                                Número de operación (si corresponde):
                            </label>

                            <input
                                type="text"
                                name="num_operacion"
                                id="num_operacion"
                                class="form-control"
                                maxlength="50"
                                placeholder="Número de referencia"
                            >
                        </div>

                        <button type="submit"
                                class="btn btn-success btn-block">
                            💳 Finalizar compra
                        </button>

                    </form>

                <?php else: ?>

                    <div class="alerta-box alerta-warning">
                        El bibliotecario debe utilizar el módulo de ventas
                        para registrar operaciones de mostrador.
                    </div>

                <?php endif; ?>

            <?php else: ?>

                <div class="alerta-box alerta-warning">
                    Debe iniciar sesión para finalizar su compra.

                    <a href="<?php echo BASE_URL; ?>public/login">
                        Iniciar sesión
                    </a>
                </div>

            <?php endif; ?>

        </div>

    </div>

<?php endif; ?>
