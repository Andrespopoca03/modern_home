<?php
// compra_exito.php
session_start();
include 'conexion.php';

// Limpiar el carrito en la sesión
unset($_SESSION['carrito']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Exitosa - Modern Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Header y Navbar -->
    <header>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="productos.php">Productos</a></li>
                <?php
                if (isset($_SESSION['usuario'])) {
                    if ($_SESSION['tipo'] === 'cliente') {
                        echo '<li><a href="clientes.php">Mis Compras</a></li>';
                    } elseif ($_SESSION['tipo'] === 'administrador') {
                        echo '<li><a href="administrador.php">Administrador</a></li>';
                    }
                    echo '<li><a href="cerrar_sesion.php">Cerrar Sesión</a></li>';
                } else {
                    echo '<li><a href="inicia_sesion.php">Iniciar Sesión</a></li>';
                }
                ?>
            </ul>
        </nav>
    </header>

    <!-- Sección de Éxito -->
    <main>
        <section class="carrito-section">
            <h2>¡Compra Exitosa!</h2>
            <div class="facturacion-card">
                <h3>Gracias por tu compra</h3>
                <p>Pase a la sucursal más cercana para recoger su producto.</p>
                <a href="index.php" class="btn">Volver al Inicio</a>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>

</body>
</html>
