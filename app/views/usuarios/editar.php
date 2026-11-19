
<?php
$mensajeError = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

$esPropiaCuenta = (int) $usuario['id_usuario'] === (int) $_SESSION['usuario_id'];
?>

<div class="contenedor">

    <div class="pagina-header">
        <div>
            <h1>Editar Usuario</h1>
            <p>Modifica la información del usuario seleccionado.</p>
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

            <form action="<?php echo BASE_URL; ?>public/usuarios/actualizar"
                  method="POST">

                <input
                    type="hidden"
                    name="id_usuario"
                    value="<?php echo (int) $usuario['id_usuario']; ?>">

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
                            value="<?php echo htmlspecialchars($usuario['dni']); ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            class="form-control"
                            value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="apellido">Apellido *</label>
                        <input
                            type="text"
                            id="apellido"
                            name="apellido"
                            class="form-control"
                            value="<?php echo htmlspecialchars($usuario['apellido']); ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo electrónico *</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            value="<?php echo htmlspecialchars($usuario['email']); ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="password">Nueva contraseña</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            minlength="6"
                            placeholder="Dejar vacío para conservar la actual">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input
                            type="text"
                            id="telefono"
                            name="telefono"
                            class="form-control"
                            value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input
                            type="text"
                            id="direccion"
                            name="direccion"
                            class="form-control"
                            value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="id_rol">Rol *</label>

                        <select
                            id="id_rol"
                            name="id_rol"
                            class="form-control"
                            required
                            <?php echo $esPropiaCuenta ? 'disabled' : ''; ?>>

                            <?php foreach ($roles as $rol): ?>
                                <option
                                    value="<?php echo (int) $rol['id_rol']; ?>"
                                    <?php echo (int) $usuario['id_rol'] === (int) $rol['id_rol'] ? 'selected' : ''; ?>>

                                    <?php echo htmlspecialchars($rol['nombre']); ?>

                                </option>
                            <?php endforeach; ?>

                        </select>

                        <?php if ($esPropiaCuenta): ?>
                            <input
                                type="hidden"
                                name="id_rol"
                                value="<?php echo (int) $usuario['id_rol']; ?>">

                            <small>No puedes cambiar tu propio rol.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado *</label>

                        <select
                            id="estado"
                            name="estado"
                            class="form-control"
                            required
                            <?php echo $esPropiaCuenta ? 'disabled' : ''; ?>>

                            <option value="ACTIVO"
                                <?php echo $usuario['estado'] === 'ACTIVO' ? 'selected' : ''; ?>>
                                ACTIVO
                            </option>

                            <option value="INACTIVO"
                                <?php echo $usuario['estado'] === 'INACTIVO' ? 'selected' : ''; ?>>
                                INACTIVO
                            </option>

                        </select>

                        <?php if ($esPropiaCuenta): ?>
                            <input
                                type="hidden"
                                name="estado"
                                value="ACTIVO">

                            <small>No puedes desactivar tu propia cuenta.</small>
                        <?php endif; ?>
                    </div>

                </div>

                <p class="form-text">
                    * Campos obligatorios. La nueva contraseña es opcional.
                </p>

                <div class="form-actions">

                    <button type="submit" class="btn btn-primary">
                        Guardar Cambios
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
