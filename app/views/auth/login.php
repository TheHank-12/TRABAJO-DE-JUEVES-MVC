<div class="form-card" style="margin-top:2rem;margin-bottom:2rem;">
    <div style="text-align:center;margin-bottom:1.5rem;">
        <img src="<?php echo BASE_URL; ?>public/assets/img/logo.png" alt="Logo Librería" style="height:65px;margin-bottom:.5rem;" onerror="this.style.display='none'">
        <h2>Acceso al Sistema</h2>
        <p style="color:var(--text-muted);font-size:.9rem;">Ingrese sus credenciales para acceder a la librería</p>
    </div>
    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger"><div class="alerta-icono">❌</div><div class="alerta-contenido"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div></div>
    <?php endif; ?>
    <form action="<?php echo BASE_URL; ?>public/login" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="ejemplo@libreria.com" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top:1.5rem;">🔑 Iniciar Sesión</button>
    </form>
    <div style="text-align:center;margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border-color);font-size:.9rem;">
        <p>¿No tiene una cuenta aún? <a href="<?php echo BASE_URL; ?>public/registro" style="color:var(--primary);font-weight:600;">Regístrese aquí</a></p>
    </div>
</div>
