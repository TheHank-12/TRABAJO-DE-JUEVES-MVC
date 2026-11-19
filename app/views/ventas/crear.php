
<style>
/* =====================================================
   PUNTO DE VENTA - IDENTIDAD VISUAL DEL SISTEMA
   ===================================================== */

.pos-page {
    width: 92%;
    max-width: 1100px;
    margin: 0 auto;
    padding: 25px 0 40px;
    color: var(--text-dark);
}

.pos-page .pos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.pos-page .pos-header h2 {
    color: var(--primary);
    font-size: 1.7rem;
    margin-bottom: 4px;
}

.pos-page .pos-header p {
    color: var(--text-muted);
    margin: 0;
}

.pos-page .pos-panel {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    margin-bottom: 22px;
    overflow: hidden;
}

.pos-page .pos-panel-header {
    background: var(--secondary);
    color: #fff;
    padding: 15px 22px;
    font-weight: 700;
    border-left: 5px solid var(--accent);
}

.pos-page .pos-panel-body {
    padding: 22px;
}

.pos-page .pos-field {
    margin-bottom: 18px;
}

.pos-page .pos-field:last-child {
    margin-bottom: 0;
}

.pos-page .pos-label {
    display: block;
    font-weight: 600;
    font-size: .9rem;
    margin-bottom: 7px;
    color: var(--text-dark);
}

.pos-page .pos-control {
    width: 100%;
    padding: .65rem .9rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    font-size: .95rem;
    color: var(--text-dark);
    background: #fff;
    transition: var(--transition);
}

.pos-page .pos-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(139,0,0,.1);
}

.pos-page .pos-help {
    display: block;
    margin-top: 6px;
    color: var(--text-muted);
    font-size: .82rem;
}

.pos-page .pos-new-client {
    padding: 20px;
    background: var(--bg-light);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    margin-top: 15px;
}

.pos-page .pos-new-client h5 {
    color: var(--primary);
    font-weight: 700;
    margin-bottom: 18px;
}

.pos-page .pos-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 20px;
}

.pos-page .pos-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    padding: 18px 20px;
    margin-top: 20px;
    background: var(--bg-light);
    border: 1px solid var(--border-color);
    border-left: 5px solid var(--accent);
    border-radius: var(--radius);
}

.pos-page .pos-summary-label {
    font-weight: 700;
    color: var(--secondary);
}

.pos-page .pos-summary-total {
    color: var(--primary);
    font-size: 1.5rem;
    font-weight: 800;
}

.pos-page .pos-actions {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 25px;
}

.pos-page .pos-actions .btn {
    min-width: 145px;
}

.pos-page .pos-empty {
    padding: 12px;
    background: #fff8e5;
    border: 1px solid #ead8a3;
    border-radius: var(--radius);
    color: #765c17;
    font-size: .9rem;
}

@media(max-width: 600px) {
    .pos-page {
        width: 95%;
    }

    .pos-page .pos-panel-body {
        padding: 16px;
    }

    .pos-page .pos-grid {
        grid-template-columns: 1fr;
    }

    .pos-page .pos-header h2 {
        font-size: 1.4rem;
    }

    .pos-page .pos-actions {
        flex-direction: column;
    }

    .pos-page .pos-actions .btn {
        width: 100%;
    }
}
</style>

<div class="pos-page">

    <!-- ENCABEZADO -->
    <div class="pos-header">

        <div>
            <h2>Nueva venta</h2>
            <p>Registre una venta desde el punto de atención.</p>
        </div>

        <a href="<?= BASE_URL ?>public/ventas"
           class="btn btn-secondary">
            ← Volver al historial
        </a>

    </div>

    <form method="POST"
      action="<?= BASE_URL ?>public/ventas/guardar"
      id="formVenta">

        <input type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(generar_token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
        <!-- 1. CLIENTE -->
        <section class="pos-panel">

            <div class="pos-panel-header">
                1. Datos del cliente
            </div>

            <div class="pos-panel-body">

                <div class="pos-field">

                    <label for="tipo_cliente" class="pos-label">
                        Tipo de cliente
                    </label>

                    <select name="tipo_cliente"
                            id="tipo_cliente"
                            class="pos-control"
                            onchange="cambiarTipoCliente()"
                            required>

                        <option value="EXISTENTE">
                            Cliente registrado
                        </option>

                        <option value="NUEVO">
                            Registrar nuevo cliente
                        </option>

                    </select>

                </div>

                <!-- CLIENTE EXISTENTE -->
                <div id="cliente_existente">

                    <div class="pos-field">

                        <label for="id_usuario" class="pos-label">
                            Seleccionar cliente
                        </label>

                        <select name="id_usuario"
                                id="id_usuario"
                                class="pos-control"
                                required>

                            <option value="">
                                Seleccione un cliente
                            </option>

                            <?php foreach ($clientes as $cliente): ?>

                                <option value="<?= (int)$cliente['id_usuario'] ?>">

                                    <?= htmlspecialchars(
                                        $cliente['dni'] . ' - ' .
                                        $cliente['nombre'] . ' ' .
                                        $cliente['apellido'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

                <!-- CLIENTE NUEVO -->
                <div id="cliente_nuevo"
                     class="pos-new-client"
                     style="display:none;">

                    <h5>Datos del nuevo cliente</h5>

                    <div class="pos-grid">

                        <div class="pos-field">

                            <label for="dni" class="pos-label">
                                DNI *
                            </label>

                            <input type="text"
                                   name="dni"
                                   id="dni"
                                   class="pos-control"
                                   maxlength="20"
                                   placeholder="Ingrese el DNI">

                        </div>

                        <div class="pos-field">

                            <label for="telefono" class="pos-label">
                                Teléfono
                            </label>

                            <input type="text"
                                   name="telefono"
                                   id="telefono"
                                   class="pos-control"
                                   maxlength="30"
                                   placeholder="Ingrese el teléfono">

                        </div>

                        <div class="pos-field">

                            <label for="nombre" class="pos-label">
                                Nombre *
                            </label>

                            <input type="text"
                                   name="nombre"
                                   id="nombre"
                                   class="pos-control"
                                   placeholder="Ingrese el nombre">

                        </div>

                        <div class="pos-field">

                            <label for="apellido" class="pos-label">
                                Apellido *
                            </label>

                            <input type="text"
                                   name="apellido"
                                   id="apellido"
                                   class="pos-control"
                                   placeholder="Ingrese el apellido">

                        </div>

                    </div>

                    <span class="pos-help">
                        El sistema generará una cuenta de cliente con los datos proporcionados.
                    </span>

                </div>

            </div>

        </section>

        <!-- 2. LIBRO -->
        <section class="pos-panel">

            <div class="pos-panel-header">
                2. Selección del libro
            </div>

            <div class="pos-panel-body">

                <?php if (empty($libros)): ?>

                    <div class="pos-empty">
                        No hay libros disponibles para realizar una venta.
                    </div>

                <?php else: ?>

                    <div class="pos-field">

                        <label for="id_libro" class="pos-label">
                            Libro disponible
                        </label>

                        <select name="id_libro"
                                id="id_libro"
                                class="pos-control"
                                required
                                onchange="actualizarResumen()">

                            <option value=""
                                    data-precio="0"
                                    data-stock="0">
                                Seleccione un libro
                            </option>

                            <?php foreach ($libros as $libro): ?>

                                <option
                                    value="<?= (int)$libro['id_libro'] ?>"
                                    data-precio="<?= (float)$libro['precio_venta'] ?>"
                                    data-stock="<?= (int)$libro['stock'] ?>"
                                >

                                    <?= htmlspecialchars(
                                        $libro['titulo'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                    - S/ <?= number_format(
                                        (float)$libro['precio_venta'],
                                        2
                                    ) ?>

                                    (Stock: <?= (int)$libro['stock'] ?>)

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="pos-field">

                        <label for="cantidad" class="pos-label">
                            Cantidad
                        </label>

                        <input type="number"
                               name="cantidad"
                               id="cantidad"
                               class="pos-control"
                               value="1"
                               min="1"
                               required
                               oninput="actualizarResumen()">

                        <span class="pos-help" id="stock_disponible">
                            Seleccione un libro para consultar su stock.
                        </span>

                    </div>

                    <div class="pos-summary">

                        <span class="pos-summary-label">
                            Total de la venta
                        </span>

                        <span class="pos-summary-total"
                              id="total_venta">
                            S/ 0.00
                        </span>

                    </div>

                    <span class="pos-help">
                        El importe definitivo se calcula nuevamente en el servidor.
                    </span>

                <?php endif; ?>

            </div>

        </section>

        <!-- 3. PAGO -->
        <section class="pos-panel">

            <div class="pos-panel-header">
                3. Información del pago
            </div>

            <div class="pos-panel-body">

                <div class="pos-field">

                    <label for="metodo_pago" class="pos-label">
                        Método de pago
                    </label>

                    <select name="metodo_pago"
                            id="metodo_pago"
                            class="pos-control"
                            required>

                        <option value="">
                            Seleccione un método de pago
                        </option>

                        <option value="EFECTIVO">Efectivo</option>
                        <option value="YAPE">Yape</option>
                        <option value="PLIN">Plin</option>
                        <option value="TARJETA">Tarjeta</option>
                        <option value="TRANSFERENCIA">Transferencia</option>

                    </select>

                </div>

                <div class="pos-field">

                    <label for="num_operacion" class="pos-label">
                        Número de operación (opcional)
                    </label>

                    <input type="text"
                           name="num_operacion"
                           id="num_operacion"
                           class="pos-control"
                           maxlength="100"
                           placeholder="Ingrese el número de operación si corresponde">

                </div>

            </div>

        </section>

        <!-- ACCIONES -->
        <div class="pos-actions">

            <a href="<?= BASE_URL ?>public/ventas"
               class="btn btn-secondary">
                Cancelar
            </a>

            <button type="submit"
                    class="btn btn-primary"
                    <?= empty($libros) ? 'disabled' : '' ?>>
                Registrar venta
            </button>

        </div>

    </form>

</div>

<script>
function cambiarTipoCliente() {

    const tipo = document.getElementById('tipo_cliente').value;

    const existente = document.getElementById('cliente_existente');
    const nuevo = document.getElementById('cliente_nuevo');

    const selector = document.getElementById('id_usuario');

    const dni = document.getElementById('dni');
    const nombre = document.getElementById('nombre');
    const apellido = document.getElementById('apellido');

    if (tipo === 'NUEVO') {

        existente.style.display = 'none';
        nuevo.style.display = 'block';

        selector.required = false;
        selector.disabled = true;

        dni.required = true;
        nombre.required = true;
        apellido.required = true;

    } else {

        existente.style.display = 'block';
        nuevo.style.display = 'none';

        selector.disabled = false;
        selector.required = true;

        dni.required = false;
        nombre.required = false;
        apellido.required = false;
    }
}

function actualizarResumen() {

    const select = document.getElementById('id_libro');
    const opcion = select.options[select.selectedIndex];

    const precio = parseFloat(opcion.dataset.precio || 0);
    const stock = parseInt(opcion.dataset.stock || 0);

    const cantidad = document.getElementById('cantidad');

    let cantidadValor = parseInt(cantidad.value || 0);

    cantidad.max = stock || 1;

    if (stock > 0 && cantidadValor > stock) {
        cantidad.value = stock;
        cantidadValor = stock;
    }

    document.getElementById('stock_disponible').textContent =
        select.value
            ? 'Stock disponible: ' + stock + ' unidades.'
            : 'Seleccione un libro para consultar su stock.';

    document.getElementById('total_venta').textContent =
        'S/ ' + (precio * cantidadValor).toFixed(2);
}

document.getElementById('formVenta').addEventListener('submit', function(event) {

    const select = document.getElementById('id_libro');
    const opcion = select.options[select.selectedIndex];

    const stock = parseInt(opcion.dataset.stock || 0);
    const cantidad = parseInt(document.getElementById('cantidad').value || 0);

    if (!select.value || cantidad <= 0 || cantidad > stock) {

        event.preventDefault();

        alert('Seleccione un libro y una cantidad válida según el stock disponible.');

        return;
    }

    if (!confirm('¿Está seguro de registrar esta venta?')) {
        event.preventDefault();
    }

});
</script>
