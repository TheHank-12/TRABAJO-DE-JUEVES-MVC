
<?php
$libros = $libros ?? [];
$id_libro = (int) ($id_libro ?? 0);
$csrf_token = $csrf_token ?? '';
?>

<div class="pagina-header">
    <div>
        <h1>Solicitar alquiler</h1>
        <p>Completa los datos para alquilar el libro que deseas.</p>
    </div>

    <a href="<?= BASE_URL ?>public/catalogo" class="btn btn-secondary">
        Volver al catálogo
    </a>
</div>

<div class="formulario-contenedor">

    <?php if (!empty($libros)): ?>

        <form action="<?= BASE_URL ?>public/solicitar-alquiler/guardar" method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($csrf_token) ?>"
            >

            <div class="form-group">
                <label for="id_libro">Seleccionar libro</label>

                <select name="id_libro" id="id_libro" required>
                    <option value="">Seleccione un libro</option>

                    <?php foreach ($libros as $libro): ?>
                        <option
                            value="<?= (int) $libro['id_libro'] ?>"
                            data-precio="<?= (float) $libro['precio_alquiler'] ?>"
                            <?= $id_libro === (int) $libro['id_libro'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($libro['titulo']) ?>
                            - Stock: <?= (int) $libro['stock'] ?>
                            - Alquiler: S/ <?= number_format((float) $libro['precio_alquiler'], 2) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="form-group">
                <label for="fecha_inicio">Fecha de inicio</label>

                <input
                    type="date"
                    name="fecha_inicio"
                    id="fecha_inicio"
                    value="<?= date('Y-m-d') ?>"
                    min="<?= date('Y-m-d') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="dias_alquiler">Días de alquiler</label>

                <input
                    type="number"
                    name="dias_alquiler"
                    id="dias_alquiler"
                    min="1"
                    max="30"
                    value="7"
                    required
                >
            </div>

            <div class="form-group">
                <label>Precio estimado</label>
                <p id="precio_estimado">Seleccione un libro</p>
            </div>

            <div class="form-group">
                <label for="metodo_pago">Método de pago</label>

                <select name="metodo_pago" id="metodo_pago" required>
                    <option value="">Seleccione un método</option>
                    <option value="EFECTIVO">Efectivo</option>
                    <option value="YAPE">Yape</option>
                    <option value="PLIN">Plin</option>
                    <option value="TARJETA">Tarjeta</option>
                    <option value="TRANSFERENCIA">Transferencia</option>
                </select>
            </div>

            <div class="form-group">
                <label for="num_operacion">Número de operación</label>

                <input
                    type="text"
                    name="num_operacion"
                    id="num_operacion"
                    placeholder="Opcional"
                >
            </div>

            <button type="submit" class="btn btn-primary">
                Solicitar alquiler
            </button>

            <a href="<?= BASE_URL ?>public/catalogo" class="btn btn-secondary">
                Cancelar
            </a>

        </form>

    <?php else: ?>

        <p>No hay libros disponibles para alquilar actualmente.</p>

        <a href="<?= BASE_URL ?>public/catalogo" class="btn btn-primary">
            Volver al catálogo
        </a>

    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const libroSelect = document.getElementById('id_libro');
    const diasInput = document.getElementById('dias_alquiler');
    const precioEstimado = document.getElementById('precio_estimado');

    function calcularPrecio() {

        if (!libroSelect || !diasInput || !precioEstimado) {
            return;
        }

        const opcion = libroSelect.options[libroSelect.selectedIndex];

        const precioDiario = parseFloat(opcion.dataset.precio || 0);
        const dias = parseInt(diasInput.value || 0);

        if (!opcion.value) {
            precioEstimado.textContent = 'Seleccione un libro';
            return;
        }

        if (dias < 1) {
            precioEstimado.textContent = 'Ingrese una cantidad válida de días';
            return;
        }

        const total = precioDiario * dias;

        precioEstimado.textContent =
            'S/ ' + total.toFixed(2) + ' (' + dias + ' días)';
    }

    if (libroSelect && diasInput) {
        libroSelect.addEventListener('change', calcularPrecio);
        diasInput.addEventListener('input', calcularPrecio);

        calcularPrecio();
    }

});
</script>
