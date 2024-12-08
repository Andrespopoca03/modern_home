<?php
// productos.php
session_start();
include 'conexion.php';

// Obtener todos los productos desde la base de datos
$sql = "SELECT * FROM Productos";
$result = $conn->query($sql);

// Verificar si hay productos disponibles
$productos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Modern Home</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Puedes agregar estilos específicos para esta página aquí */
    </style>
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
                    echo '<li><a href="cerrar_sesion.php">Cerrar Sesión</a></li>';
                } else {
                    echo '<li><a href="inicia_sesion.php">Iniciar Sesión</a></li>';
                }
                ?>
            </ul>
        </nav>
    </header>

    <!-- Sección Principal -->
    <main>
        <h1 style="text-align:center; margin:40px 0; color:#333333;">Nuestros Productos</h1>

        <!-- Barra de búsqueda -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Buscar productos..." onkeyup="filtrarProductos()">
        </div>

        <!-- Carrusel de Productos -->
        <section class="carousel-container">
            <button class="carousel-arrow left" onclick="prevSlide()">&#10094;</button>
            <div class="carousel-slide" id="carousel-slide">
                <?php foreach ($productos as $producto): ?>
                    <div class="carousel-item">
                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                        <p class="price">Precio: $<?php echo number_format($producto['precio'], 2, ',', '.'); ?></p>
                        <button onclick="agregarAlCarrito(<?php echo $producto['id']; ?>, <?php echo $producto['precio']; ?>, '<?php echo addslashes($producto['nombre']); ?>')">Agregar al Carrito</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-arrow right" onclick="nextSlide()">&#10095;</button>
        </section>

        <!-- Sección de Carrito -->
        <section id="carrito" class="carrito-section">
            <h2>Carrito de Compras</h2>
            <ul id="carrito-lista"></ul>
            <p>Total: $<span id="total">0</span></p>
            <button onclick="window.location.href='compra_productos.php'">Finalizar Compra</button>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>

    <!-- Scripts -->
    <script>
    // Función para filtrar productos en tiempo real
    function filtrarProductos() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let carouselSlide = document.getElementById('carousel-slide');
        let items = carouselSlide.getElementsByClassName('carousel-item');

        // Mostrar u ocultar productos según la búsqueda
        for (let i = 0; i < items.length; i++) {
            let nombre = items[i].getElementsByTagName('h3')[0].innerText.toLowerCase();
            if (nombre.includes(input)) {
                items[i].style.display = "flex";
            } else {
                items[i].style.display = "none";
            }
        }
    }

    // Carrusel de Productos
    let currentIndex = 0;
    const carouselSlide = document.getElementById('carousel-slide');
    const totalSlides = carouselSlide.getElementsByClassName('carousel-item').length;

    function showSlide(index) {
        if (index >= totalSlides) {
            currentIndex = 0;
        } else if (index < 0) {
            currentIndex = totalSlides - 1;
        } else {
            currentIndex = index;
        }
        carouselSlide.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    function nextSlide() {
        showSlide(currentIndex + 1);
    }

    function prevSlide() {
        showSlide(currentIndex - 1);
    }

    // Autoplay del carrusel (opcional)
    let autoplay = true;
    let autoplayInterval = setInterval(() => {
        if (autoplay) {
            nextSlide();
        }
    }, 5000); // Cambia de slide cada 5 segundos

    // Pausar autoplay al interactuar con el carrusel
    const carouselContainer = document.querySelector('.carousel-container');
    carouselContainer.addEventListener('mouseenter', () => {
        autoplay = false;
    });
    carouselContainer.addEventListener('mouseleave', () => {
        autoplay = true;
    });

    // Inicializar el carrusel
    showSlide(currentIndex);

    // Inicializa el carrito desde localStorage o como un array vacío
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

    // Función para agregar productos al carrito
    function agregarAlCarrito(productoId, precio, nombre) {
        let cantidad = 1; // Puedes permitir que el usuario seleccione la cantidad

        // Verificar si el producto ya está en el carrito
        const index = carrito.findIndex(item => item.productoId === productoId);
        if (index !== -1) {
            // Si el producto ya está en el carrito, incrementar la cantidad
            carrito[index].cantidad += cantidad;
        } else {
            // Si no está, agregarlo al carrito con la cantidad inicial
            carrito.push({ productoId, precio, nombre, cantidad });
        }

        // Actualizar el carrito en el localStorage y en la interfaz
        localStorage.setItem('carrito', JSON.stringify(carrito));
        actualizarCarrito();
    }

    // Función para eliminar productos del carrito
    function eliminarDelCarrito(index) {
        carrito.splice(index, 1); // Elimina el producto del carrito
        actualizarCarrito();
        localStorage.setItem('carrito', JSON.stringify(carrito)); // Actualiza el localStorage
    }

    // Función para actualizar la interfaz del carrito
    function actualizarCarrito() {
        const listaCarrito = document.getElementById('carrito-lista');
        const total = document.getElementById('total');
        listaCarrito.innerHTML = '';
        let sumaTotal = 0;

        carrito.forEach((item, index) => {
            const li = document.createElement('li');
            li.textContent = `${item.nombre} x${item.cantidad} - $${(item.precio * item.cantidad).toLocaleString('es-MX')}`;
            const btnEliminar = document.createElement('button');
            btnEliminar.textContent = 'Eliminar';
            btnEliminar.onclick = () => eliminarDelCarrito(index);
            li.appendChild(btnEliminar);
            listaCarrito.appendChild(li);
            sumaTotal += item.precio * item.cantidad;
        });

        total.textContent = sumaTotal.toLocaleString('es-MX');
    }

    // Inicializa la interfaz del carrito al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
        // Limpiar carrito si los datos están incompletos
        if (carrito.length > 0 && (!carrito[0].nombre || !carrito[0].cantidad)) {
            localStorage.removeItem('carrito');
            carrito = [];
        }
        actualizarCarrito();
    });
    </script>

</body>
</html>
