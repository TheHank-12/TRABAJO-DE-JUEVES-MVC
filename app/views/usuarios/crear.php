
<?php
$mensajeError = $_SESSION['error'] ?? '';
$datos = $_SESSION['datos_formulario'] ?? [];

unset($_SESSION['error'], $_SESSION['datos_formulario']);
?>

<div class="contenedor">

    <div class="pagina-header">
        <div>
            <h1>Registrar Usuario</h1>
            <p>Completa los datos para registrar un nuevo usuario.</p>
        </div>

        <a href="<?php echo BASE_URL; ?>public/usuarios"
           class="btn btn-secondary">
            Volver
        </a>
    </div>

    <?php if ($mensajeError): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($mensajeError); ?>
        </div>
    <?php endif; ?>

    <div class="card">

        <div class="card-header">
            <h3>Datos del usuario</h3>
        </div>

        <div class="card-body">

            <form action="<?php echo BASE_URL; ?>public/usuarios/guardar"
                  method="POST">

                <div class="form-grid">

                    <div class="form-group">
                        <label for="dni">DNI *</label>
                        <input
                            type="text"
                            id="dni"
                            name="dni"
                            class="form-control"
                            maxlength="8"
                            pattern="[0-9]{8}"
                            value="<?php echo htmlspecialchars($datos['dni'] ?? ''); ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            class="form-control"
                            value="<?php echo htmlspecialchars($datos['nombre'] ?? ''); ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="apellido">Apellido *</label>
                        <input
                            type="text"
                            id="apellido"
                            name="apellido"
                            class="form-control"
                            value="<?php echo htmlspecialchars($datos['apellido'] ?? ''); ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo electrónico *</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            value="<?php echo htmlspecialchars($datos['email'] ?? ''); ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            minlength="6"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input
                            type="text"
                            id="telefono"
                            name="telefono"
                            class="form-control"
                            value="<?php echo htmlspecialchars($datos['telefono'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input
                            type="text"
                            id="direccion"
                            name="direccion"
                            class="form-control"
                            value="<?php echo htmlspecialchars($datos['direccion'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="id_rol">Rol *</label>
                        <select
                            id="id_rol"
                            name="id_rol"
                            class="form-control"
                            required>

                            <option value="">Seleccionar rol</option>

                            <?php foreach ($roles as $rol): ?>
                                <option
                                    value="<?php echo (int) $rol['id_rol']; ?>"
                                    <?php echo (isset($datos['id_rol']) && (int) $datos['id_rol'] === (int) $rol['id_rol']) ? 'selected' : ''; ?>>

                                    <?php echo htmlspecialchars($rol['nombre']); ?>

                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado *</label>
                        <select
                            id="estado"
                            name="estado"
                            class="form-control"
                            required>

                            <option value="ACTIVO"
                                <?php echo ($datos['estado'] ?? 'ACTIVO') === 'ACTIVO' ? 'selected' : ''; ?>>
                                ACTIVO
                            </option>

                            <option value="INACTIVO"
                                <?php echo ($datos['estado'] ?? '') === 'INACTIVO' ? 'selected' : ''; ?>>
                                INACTIVO
                            </option>

                        </select>
                    </div>

                </div>

                <p class="form-text">* Campos obligatorios.</p>

                <div class="form-actions">

                    <button type="submit" class="btn btn-primary">
                        Registrar Usuario
                    </button>

                    <a href="<?php echo BASE_URL; ?>public/usuarios"
                       class="btn btn-secondary">
                        Cancelar
                    </a>

                </div>

            </form>

        </div>
    </div>
</div>
