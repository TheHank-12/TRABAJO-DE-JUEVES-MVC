
<?php
// Evita errores si el controlador no envía datos a la vista.
$datos = is_array($datos ?? null) ? $datos : [];

$camposRegistro = [
    'dni',
    'telefono',
    'nombre',
    'apellido',
    'email',
    'direccion'
];

// Si un campo viene vacío o es null, se convierte en texto vacío.
foreach ($camposRegistro as $campoRegistro) {
    $datos[$campoRegistro] = (string) ($datos[$campoRegistro] ?? '');
}

// Asegura que el token CSRF siempre sea una cadena.
$csrf_token = (string) ($csrf_token ?? '');
?>

<div class="form-card" style="margin-top:2rem;margin-bottom:2rem;">

    <div style="text-align:center;margin-bottom:1.5rem;">
        <h2>Crear Cuenta de Cliente</h2>
        <p style="color:var(--text-muted);font-size:.9rem;">
            Complete el formulario para comprar y alquilar libros en Soles (S/)
        </p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>public/registro" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"
        >

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

            <div class="form-group">
                <label for="dni">DNI / Documento (*):</label>
                <input
                    type="text"
                    name="dni"
                    id="dni"
                    class="form-control"
                    maxlength="15"
                    value="<?php echo htmlspecialchars($datos['dni'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono / Celular:</label>
                <input
                    type="text"
                    name="telefono"
                    id="telefono"
                    class="form-control"
                    value="<?php echo htmlspecialchars($datos['telefono'], ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>

        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

            <div class="form-group">
                <label for="nombre">Nombres (*):</label>
                <input
                    type="text"
                    name="nombre"
                    id="nombre"
                    class="form-control"
                    value="<?php echo htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="apellido">Apellidos (*):</label>
                <input
                    type="text"
                    name="apellido"
                    id="apellido"
                    class="form-control"
                    value="<?php echo htmlspecialchars($datos['apellido'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>

        </div>

        <div class="form-group">
            <label for="email">Correo Electrónico (*):</label>
            <input
                type="email"
                name="email"
                id="email"
                class="form-control"
                value="<?php echo htmlspecialchars($datos['email'], ENT_QUOTES, 'UTF-8'); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="direccion">Dirección de Domicilio / Residencia:</label>
            <input
                type="text"
                name="direccion"
                id="direccion"
                class="form-control"
                value="<?php echo htmlspecialchars($datos['direccion'], ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

            <div class="form-group">
                <label for="password">Contraseña (*):</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    minlength="6"
                    placeholder="Mínimo 6 caracteres"
                    required
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña (*):</label>
                <input
                    type="password"
                    name="confirm_password"
                    id="confirm_password"
                    class="form-control"
                    minlength="6"
                    placeholder="Repita la contraseña"
                    required
                >
            </div>

        </div>

        <button
            type="submit"
            class="btn btn-success btn-block"
            style="margin-top:1.5rem;"
        >
            📝 Registrarme Ahora
        </button>

    </form>

    <div style="text-align:center;margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border-color);font-size:.9rem;">
        <p>
            ¿Ya posee una cuenta?
            <a
                href="<?php echo BASE_URL; ?>public/login"
                style="color:var(--primary);font-weight:600;"
            >
                Iniciar Sesión
            </a>
        </p>
    </div>

</div>
