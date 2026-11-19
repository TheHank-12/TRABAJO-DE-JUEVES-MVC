
<?php

require_once dirname(__DIR__, 3) . '/config/sesion.php';

$usuario_actual = obtener_usuario_actual();
$cant_carrito = obtener_conteo_carrito();

// Token CSRF para el cierre de sesión MVC.
if (esta_autenticado() && empty($_SESSION['csrf_auth'])) {
    $_SESSION['csrf_auth'] = bin2hex(random_bytes(32));
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php
        echo isset($titulo_pagina)
            ? htmlspecialchars($titulo_pagina) . ' - Librería Perú'
            : 'Librería Perú - Ventas y Alquileres';
        ?>
    </title>

    <link
        rel="stylesheet"
        href="<?php echo BASE_URL; ?>public/assets/css/style.css"
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >
</head>

<body>

<header class="navbar-header">

    <div class="contenedor navbar-container">

        <a href="<?php echo BASE_URL; ?>public/" class="brand-logo">

            <img
                src="<?php echo BASE_URL; ?>public/assets/img/logo.png"
                alt="Logo Librería"
                onerror="this.src='<?php echo BASE_URL; ?>public/assets/img/no-cover.png.jpg'"
            >

            <span class="brand-name">
                Librería<span class="brand-accent">Vallejo</span>
            </span>

        </a>

        <button
            class="menu-toggle"
            id="btn-menu-toggle"
            aria-label="Abrir Menú"
        >
            ☰
        </button>

        <nav class="nav-menu" id="nav-menu-main">

            <ul class="nav-list">

                <!-- Acceso público -->
                <li>
                    <a href="<?php echo BASE_URL; ?>public/" class="nav-link">
                        Catálogo
                    </a>
                </li>

                <!-- Menú para administrador y bibliotecario -->
                <?php if (es_personal()): ?>

                    <li>
                        <a href="<?php echo BASE_URL; ?>public/libros" class="nav-link">
                            Libros
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>public/categorias" class="nav-link">
                            Categorías
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>public/autores" class="nav-link">
                            Autores
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>public/editoriales" class="nav-link">
                            Editoriales
                        </a>
                    </li>

                    <!-- Solo administrador -->
                    <?php if (es_admin()): ?>

                        <li>
                            <a href="<?php echo BASE_URL; ?>public/usuarios" class="nav-link">
                                Usuarios
                            </a>
                        </li>

                    <?php endif; ?>

                    <!-- Módulos operativos anteriores -->
                    <li>
                        <a href="<?php echo BASE_URL; ?>public/alquileres" class="nav-link">
                            Alquileres
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>public/ventas" class="nav-link">
                            Ventas
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>public/kardex" class="nav-link">
                            Kardex
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>public/reportes" class="nav-link">
                            Reportes
                        </a>
                    </li>

                <?php endif; ?>

                <!-- Menú para clientes -->
                <?php if (esta_autenticado() && !es_personal()): ?>

                    <li>
                        <a href="<?php echo BASE_URL; ?>public/mis-alquileres" class="nav-link">
                            Mis Alquileres
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>public/mis-compras" class="nav-link">
                            Mis Compras
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>public/mis-multas" class="nav-link">
                            Mis Multas
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

            <div class="nav-user-actions">

                <a href="<?php echo BASE_URL; ?>public/carrito"
                class="btn-cart-badge">

                    🛒 Carrito

                    <span class="badge-count" id="cart-counter-badge">
                        <?php echo $cant_carrito; ?>
                    </span>
                </a>

                <?php if (esta_autenticado()): ?>

                    <div class="user-pill">

                        <span class="user-role-badge <?php echo es_personal() ? 'badge-admin' : 'badge-client'; ?>">

                            <?php
                            echo htmlspecialchars(
                                $usuario_actual['rol'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>

                        </span>

                        <span class="user-name">

                            <?php
                            echo htmlspecialchars(
                                $usuario_actual['nombre'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>

                        </span>

                        <form
                            action="<?php echo BASE_URL; ?>public/logout"
                            method="POST"
                            style="display:inline;"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_auth'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            >

                            <button
                                type="submit"
                                class="btn-logout"
                                title="Cerrar Sesión"
                            >
                                🚪 Salir
                            </button>

                        </form>

                    </div>

                <?php else: ?>

                    <a
                        href="<?php echo BASE_URL; ?>public/login"
                        class="btn-login"
                    >
                        Ingresar
                    </a>

                    <a
                        href="<?php echo BASE_URL; ?>public/registro"
                        class="btn-register"
                    >
                        Registrarse
                    </a>

                <?php endif; ?>

            </div>

        </nav>

    </div>

</header>

<main class="contenedor principal-content">

    <?php include_once __DIR__ . '/alerta.php'; ?>
