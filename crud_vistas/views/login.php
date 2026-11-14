<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEx+ - Iniciar Sesión</title>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/login_y_registro.css">
</head>

<body class="pagina-acceso">

    <main class="tarjeta-acceso">

        <!-- Logo y Título -->
        <header class="cabecera-tarjeta">
            <i class="bi bi-heart-pulse icono-logo"></i>
            <h3 class="titulo-tarjeta">Sistema De Gestión</h3>
            <p class="text-muted small mb-0">Iniciar Sesión</p>
        </header>

        <!-- Mensaje de error (si falla el login) -->
        <?php if (isset($_GET['error'])): ?>
            <div class="mensaje-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>
                    <?php
                    if ($_GET['error'] === 'credenciales_incorrectas') {
                        echo 'Usuario o contraseña incorrectos.';
                    } elseif ($_GET['error'] === 'cuenta_inactiva') {
                        echo 'Esta cuenta se encuentra inactiva.';
                    } else {
                        echo 'Error al iniciar sesión.';
                    }
                    ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="../controllers/loginController.php" method="POST">

            <div class="grupo-campo">
                <label for="username" class="etiqueta-campo">Nombre de Usuario</label>
                <i class="bi bi-person icono-campo"></i>
                <input type="text" name="username" class="campo-texto" id="username" maxlength="30"
                       placeholder="Ingresa tu usuario (ej: adminPRO)" required>
            </div>

            <div class="grupo-campo">
                <label for="password" class="etiqueta-campo">Contraseña</label>
                <i class="bi bi-lock icono-campo"></i>
                <input type="password" name="password" class="campo-texto" id="password"
                       placeholder="••••••••" required>
            </div>

            <button type="submit" class="boton-primario">
                Ingresar al sistema <i class="bi bi-arrow-right"></i>
            </button>

        </form>

        <footer class="pie-tarjeta">
            <a href="#" class="enlace-olvido">Restablecer contraseña</a>
        </footer>

    </main>

</body>
</html>
