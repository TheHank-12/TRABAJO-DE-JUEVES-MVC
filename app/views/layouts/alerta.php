
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['mensaje']) && !empty($_SESSION['mensaje'])):

    // Usar nombres exclusivos para evitar conflictos con las vistas.
    $tipo_alerta = $_SESSION['tipo_mensaje'] ?? 'info';
    $mensaje_alerta = $_SESSION['mensaje'];

    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
?>
    <div class="alerta-box alerta-<?php echo htmlspecialchars($tipo_alerta, ENT_QUOTES, 'UTF-8'); ?>" id="alerta-sistema">

        <div class="alerta-icono">
            <?php
            switch ($tipo_alerta) {
                case 'success':
                    echo '✅';
                    break;

                case 'danger':
                    echo '❌';
                    break;

                case 'warning':
                    echo '⚠️';
                    break;

                default:
                    echo 'ℹ️';
                    break;
            }
            ?>
        </div>

        <div class="alerta-contenido">
            <?php echo htmlspecialchars($mensaje_alerta, ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <button
            type="button"
            class="alerta-cerrar"
            onclick="document.getElementById('alerta-sistema').remove();"
        >
            &times;
        </button>

    </div>
<?php endif; ?>
