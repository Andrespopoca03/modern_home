<?php
// index.php
session_start();
include 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Modern Home, muebles y accesorios para el hogar.">
    <title>Modern Home - Muebles y Decoración</title>
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
                    // Verificar el tipo de usuario y mostrar la opción correspondiente
                    if ($_SESSION['tipo'] === 'cliente') {
                        echo '<li><a href="clientes.php">Mis Compras</a></li>';
                    } elseif ($_SESSION['tipo'] === 'administrador') {
                        echo '<li><a href="administrador.php">Administrador</a></li>';
                    }
                    // Opción para cerrar sesión
                    echo '<li><a href="cerrar_sesion.php">Cerrar Sesión</a></li>';
                } else {
                    // Opción para iniciar sesión si no hay un usuario activo
                    echo '<li><a href="inicia_sesion.php">Iniciar Sesión</a></li>';
                }
                ?>
            </ul>
        </nav>
    </header>

    <!-- Sección Hero -->
    <main>
        <section class="hero">
            <h1>Bienvenidos a Modern Home</h1>
            <p class="animated-text">
                <span class="card-animation">
                    <div class="loader">
                        <span>Descubre</span>
                        <span class="words">
                            <span class="word">muebles modernos</span>
                            <span class="word">accesorios</span>
                            <span class="word">que transformarán</span>
                            <span class="word">tu hogar.</span>
                            <span class="word">muebles modernos</span>
                            <span class="word">accesorios</span>
                        </span>
                    </div>
                </span>
            </p>
            <a href="productos.php" class="btn">Ver Productos</a>
        </section>

        <!-- Sección de Información -->
        <section class="info-section">
            <div class="info-item">
                <h2>Nuestros Productos</h2>
                <p>En Modern Home ofrecemos una amplia gama de muebles y accesorios con diseños exclusivos, ideales para renovar cualquier espacio de tu hogar.</p>
                <img src="img/livingroom.jpeg" alt="Sala moderna">
            </div>

            <div class="info-item">
                <h2>Ubicación</h2>
                <p>Encuéntranos en Cuernavaca, Morelos. Visítanos en nuestra tienda principal en la calle Ejemplo #123, Colonia Centro, Cuernavaca.</p>
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3772.3528713474087!2d-99.23723278549512!3d18.924474261022204!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85cddf1f74fd97cf%3A0xc51f58db5c79bc7e!2sCentro%20de%20Cuernavaca%2C%20Morelos!5e0!3m2!1ses!2smx!4v1696864531845!5m2!1ses!2smx" 
                        width="100%" 
                        height="250" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </section>

        <!-- Sección Cómo Funcionamos con Tarjeta Animada -->
        <section class="funcionamos-section">
            <h2>Cómo Funcionamos</h2>
            <div class="card">
                <div class="content">
                    <svg
                      fill="currentColor"
                      viewBox="0 0 24 24"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M20 9V5H4V9H20ZM20 11H4V19H20V11ZM3 3H21C21.5523 3 22 3.44772 22 4V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V4C2 3.44772 2.44772 3 3 3ZM5 12H8V17H5V12ZM5 6H7V8H5V6ZM9 6H11V8H9V6Z"
                      ></path>
                    </svg>
                    <p class="para">
                      En Modern Home priorizamos la satisfacción de nuestros clientes, ofreciendo productos de alta calidad y asesoría en decoración. Nuestro objetivo es ayudarte a crear un hogar acogedor y moderno.
                    </p>
                    
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>
</body>
</html>
