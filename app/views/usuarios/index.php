
<?php
$mensajeError = $_SESSION['error'] ?? '';
$mensajeExito = $_SESSION['exito'] ?? '';

unset($_SESSION['error'], $_SESSION['exito']);
?>

<div class="contenedor">

    <div class="pagina-header">
        <div>
            <h1>Gestión de Usuarios</h1>
            <p>Administra los usuarios registrados en el sistema.</p>
        </div>

        <a href="<?php echo BASE_URL; ?>public/usuarios/crear"
           class="btn btn-primary">
            + Registrar Usuario
        </a>
    </div>

    <?php if ($mensajeError): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($mensajeError); ?>
        </div>
    <?php endif; ?>

    <?php if ($mensajeExito): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($mensajeExito); ?>
        </div>
    <?php endif; ?>

    <div class="card">

        <div class="card-header">
            <h3>Usuarios registrados</h3>

            <input
                type="text"
                id="input-search-live"
                class="form-control"
                placeholder="Buscar por nombre, DNI, correo o rol..."
            >
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>DNI</th>
                        <th>Nombre completo</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="tabla-usuarios">

                    <?php if (!empty($usuarios)): ?>

                        <?php foreach ($usuarios as $usuario): ?>

                            <tr>
                                <td>
                                    <?php echo (int) $usuario['id_usuario']; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($usuario['dni']); ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $usuario['nombre'] . ' ' . $usuario['apellido']
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($usuario['email']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($usuario['telefono'] ?? '—'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($usuario['rol_nombre']); ?>
                                </td>

                                <td>
                                    <?php if ($usuario['estado'] === 'ACTIVO'): ?>
                                        <span class="badge badge-success">ACTIVO</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">INACTIVO</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php
                                    echo !empty($usuario['fecha_registro'])
                                        ? htmlspecialchars($usuario['fecha_registro'])
                                        : '—';
                                    ?>
                                </td>

                                <td>
                                    <div class="acciones">

                                        <a
                                            href="<?php echo BASE_URL; ?>public/usuarios/editar?id=<?php echo (int) $usuario['id_usuario']; ?>"
                                            class="btn btn-sm btn-warning">
                                            Editar
                                        </a>

                                        <?php if ((int) $usuario['id_usuario'] !== (int) $_SESSION['usuario_id']): ?>

                                            <form
                                                action="<?php echo BASE_URL; ?>public/usuarios/estado"
                                                method="POST"
                                                style="display:inline;"
                                                onsubmit="return confirm('¿Deseas cambiar el estado de este usuario?');">

                                                <input
                                                    type="hidden"
                                                    name="id_usuario"
                                                    value="<?php echo (int) $usuario['id_usuario']; ?>">

                                                <button type="submit" class="btn btn-sm btn-secondary">
                                                    <?php echo $usuario['estado'] === 'ACTIVO' ? 'Desactivar' : 'Activar'; ?>
                                                </button>
                                            </form>

                                            <form
                                                action="<?php echo BASE_URL; ?>public/usuarios/eliminar"
                                                method="POST"
                                                style="display:inline;"
                                                onsubmit="return confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.');">

                                                <input
                                                    type="hidden"
                                                    name="id_usuario"
                                                    value="<?php echo (int) $usuario['id_usuario']; ?>">

                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    Eliminar
                                                </button>
                                            </form>

                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9" class="text-center">
                                No hay usuarios registrados.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const buscador = document.getElementById('input-search-live');
    const filas = document.querySelectorAll('#tabla-usuarios tr');

    buscador.addEventListener('input', function () {

        const texto = this.value.toLowerCase().trim();

        filas.forEach(function (fila) {

            const contenido = fila.textContent.toLowerCase();

            fila.style.display = contenido.includes(texto) ? '' : 'none';

        });

    });

});
</script>
