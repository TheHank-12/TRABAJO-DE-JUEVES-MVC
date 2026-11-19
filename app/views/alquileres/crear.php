
<?php
$clientes = $clientes ?? [];
$libros = $libros ?? [];
?>

<div class="pagina-header">
    <div>
        <h1>Registrar alquiler</h1>
        <p>Completa los datos para registrar el alquiler de un libro.</p>
    </div>

    <a href="<?= BASE_URL ?>public/alquileres" class="btn btn-secondary">
        Volver
    </a>
</div>

<div class="formulario-contenedor">
    <form action="<?= BASE_URL ?>public/alquileres/guardar" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($csrf_token ?? '') ?>"
        >
        <div class="form-group">
            <label for="id_usuario">Cliente</label>

            <select name="id_usuario" id="id_usuario" required>
                <option value="">Seleccione un cliente</option>

                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= htmlspecialchars($cliente['id_usuario']) ?>">
                        <?= htmlspecialchars(
                            $cliente['nombre'] . ' ' .
                            $cliente['apellido'] . ' - DNI: ' .
                            $cliente['dni']
                        ) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div class="form-group">
            <label for="id_libro">Libro</label>

            <select name="id_libro" id="id_libro" required>
                <option value="">Seleccione un libro</option>

                <?php foreach ($libros as $libro): ?>
                    <option value="<?= htmlspecialchars($libro['id_libro']) ?>">
                        <?= htmlspecialchars($libro['titulo']) ?>
                        - Stock: <?= htmlspecialchars($libro['stock']) ?>
                        - Alquiler: S/ <?= number_format((float)$libro['precio_alquiler'], 2) ?>
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
            Registrar alquiler
        </button>

        <a href="<?= BASE_URL ?>public/alquileres" class="btn btn-secondary">
            Cancelar
        </a>

    </form>
</div>
