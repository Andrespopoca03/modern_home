<?php
// administrar_productos.php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: inicia_sesion.php");
    exit();
}

// Variables para manejar mensajes de éxito o error
$mensaje = "";

// Manejar la acción de agregar un nuevo producto
if (isset($_POST['agregar_producto'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $imagen = trim($_POST['imagen']);
    $stock = intval($_POST['stock']);

    // Validaciones básicas
    if (empty($nombre) || empty($descripcion) || empty($precio) || empty($imagen) || empty($stock)) {
        $mensaje = "Por favor, completa todos los campos para agregar el producto.";
    } else {
        // Insertar el nuevo producto en la base de datos
        $stmt = $conn->prepare("INSERT INTO Productos (nombre, descripcion, precio, imagen, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsi", $nombre, $descripcion, $precio, $imagen, $stock);

        if ($stmt->execute()) {
            $mensaje = "Producto agregado exitosamente.";
        } else {
            $mensaje = "Error al agregar el producto: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Manejar la acción de eliminar un producto
if (isset($_GET['eliminar'])) {
    $id_producto = intval($_GET['eliminar']);

    // Eliminar el producto de la base de datos
    $stmt = $conn->prepare("DELETE FROM Productos WHERE id = ?");
    $stmt->bind_param("i", $id_producto);

    if ($stmt->execute()) {
        $mensaje = "Producto eliminado exitosamente.";
    } else {
        $mensaje = "Error al eliminar el producto: " . $stmt->error;
    }
    $stmt->close();
}

// Manejar la acción de editar un producto
if (isset($_POST['editar_producto'])) {
    $id_producto = intval($_POST['id_producto']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $imagen = trim($_POST['imagen']);
    $stock = intval($_POST['stock']);

    // Validaciones básicas
    if (empty($nombre) || empty($descripcion) || empty($precio) || empty($imagen) || empty($stock)) {
        $mensaje = "Por favor, completa todos los campos para editar el producto.";
    } else {
        // Actualizar el producto en la base de datos
        $stmt = $conn->prepare("UPDATE Productos SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, stock = ? WHERE id = ?");
        $stmt->bind_param("ssdssi", $nombre, $descripcion, $precio, $imagen, $stock, $id_producto);

        if ($stmt->execute()) {
            $mensaje = "Producto actualizado exitosamente.";
        } else {
            $mensaje = "Error al actualizar el producto: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Obtener todos los productos para listar
$stmt = $conn->prepare("SELECT * FROM Productos ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Productos - Modern Home</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Estilos específicos para la administración de productos */
        .admin-productos {
            padding: 40px 20px;
            background-color: #ffffff;
            max-width: 1200px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.5s ease-in-out;
        }

        .admin-productos h2 {
            text-align: center;
            color: #333333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        /* Formulario de Agregar Producto */
        .admin-productos .agregar-form {
            margin-bottom: 40px;
            border: 1px solid #dddddd;
            padding: 20px;
            border-radius: 10px;
            background-color: #f9f9f9;
        }

        .admin-productos .agregar-form h3 {
            margin-bottom: 20px;
            color: #333333;
            font-size: 22px;
            text-align: center;
        }

        /* Tabla de Productos */
        .admin-productos table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-productos th, 
        .admin-productos td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dddddd;
        }

        .admin-productos th {
            background-color: #f2f2f2;
            color: #333333;
        }

        .admin-productos tr:hover {
            background-color: #f9f9f9;
        }

        .admin-productos a.btn {
            padding: 8px 12px;
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 14px;
        }

        .admin-productos a.btn:hover {
            background-color: #1e7e34;
        }

        .admin-productos .btn-editar {
            background-color: #ffc107;
        }

        .admin-productos .btn-editar:hover {
            background-color: #e0a800;
        }

        .admin-productos .btn-eliminar {
            background-color: #dc3545;
        }

        .admin-productos .btn-eliminar:hover {
            background-color: #c82333;
        }

        /* Modal para Editar Producto */
        .modal {
            display: none; /* Oculto por defecto */
            position: fixed; /* Fijo */
            z-index: 1; /* Sobre otros elementos */
            left: 0;
            top: 0;
            width: 100%; /* Ancho completo */
            height: 100%; /* Alto completo */
            overflow: auto; /* Habilita scroll si es necesario */
            background-color: rgba(0,0,0,0.4); /* Negro con opacidad */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto; /* 10% desde arriba y centrado */
            padding: 20px;
            border: 1px solid #888;
            width: 50%; /* Ancho del modal */
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <!-- Header y Navbar -->
    <header>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="clientes.php">Clientes</a></li>
                <li><a href="administrador.php">Administrador</a></li>
                <li><a href="cerrar_sesion.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <!-- Sección de Administración de Productos -->
    <main>
        <section class="admin-productos">
            <h2>Administrar Productos</h2>
            
            <!-- Mostrar mensajes de éxito o error -->
            <?php
            if (!empty($mensaje)) {
                echo "<p style='color: green; text-align: center; font-weight: bold;'>$mensaje</p>";
            }
            ?>

            <!-- Formulario para Agregar Nuevo Producto -->
            <div class="agregar-form">
                <h3>Agregar Nuevo Producto</h3>
                <form method="POST" action="administrar_productos.php">
                    <div>
                        <label for="nombre">Nombre del Producto:</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div>
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    <div>
                        <label for="precio">Precio (MXN):</label>
                        <input type="number" step="0.01" id="precio" name="precio" required>
                    </div>
                    <div>
                        <label for="imagen">URL de la Imagen:</label>
                        <input type="text" id="imagen" name="imagen" required>
                    </div>
                    <div>
                        <label for="stock">Stock Disponible:</label>
                        <input type="number" id="stock" name="stock" required>
                    </div>
                    <button type="submit" name="agregar_producto" class="btn">Agregar Producto</button>
                </form>
            </div>

            <!-- Tabla de Productos Existentes -->
            <table>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio (MXN)</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['id']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" width="60"></td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                        <td>$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                        <td>
                            <a href="administrar_productos.php?editar=<?php echo $producto['id']; ?>" class="btn btn-editar">Editar</a>
                            <a href="administrar_productos.php?eliminar=<?php echo $producto['id']; ?>" class="btn btn-eliminar" onclick="return confirm('¿Estás seguro de eliminar este producto?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>
    </main>

    <!-- Modal para Editar Producto -->
    <?php
    // Si se solicita editar un producto, obtener sus datos
    if (isset($_GET['editar'])) {
        $id_editar = intval($_GET['editar']);

        // Obtener los datos del producto a editar
        $stmt = $conn->prepare("SELECT * FROM Productos WHERE id = ?");
        $stmt->bind_param("i", $id_editar);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $producto_editar = $result->fetch_assoc();
        } else {
            echo "<script>alert('Producto no encontrado.'); window.location.href='administrar_productos.php';</script>";
            exit();
        }
        $stmt->close();
    ?>
        <div id="modalEditar" class="modal">
            <div class="modal-content">
                <span class="close" onclick="cerrarModal()">&times;</span>
                <h3>Editar Producto</h3>
                <form method="POST" action="administrar_productos.php">
                    <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($producto_editar['id']); ?>">
                    <div>
                        <label for="nombre_edit">Nombre del Producto:</label>
                        <input type="text" id="nombre_edit" name="nombre" value="<?php echo htmlspecialchars($producto_editar['nombre']); ?>" required>
                    </div>
                    <div>
                        <label for="descripcion_edit">Descripción:</label>
                        <textarea id="descripcion_edit" name="descripcion" rows="3" required><?php echo htmlspecialchars($producto_editar['descripcion']); ?></textarea>
                    </div>
                    <div>
                        <label for="precio_edit">Precio (MXN):</label>
                        <input type="number" step="0.01" id="precio_edit" name="precio" value="<?php echo htmlspecialchars($producto_editar['precio']); ?>" required>
                    </div>
                    <div>
                        <label for="imagen_edit">URL de la Imagen:</label>
                        <input type="text" id="imagen_edit" name="imagen" value="<?php echo htmlspecialchars($producto_editar['imagen']); ?>" required>
                    </div>
                    <div>
                        <label for="stock_edit">Stock Disponible:</label>
                        <input type="number" id="stock_edit" name="stock" value="<?php echo htmlspecialchars($producto_editar['stock']); ?>" required>
                    </div>
                    <button type="submit" name="editar_producto" class="btn">Actualizar Producto</button>
                </form>
            </div>
        </div>

        <script>
            // Abrir el modal automáticamente si se está editando un producto
            window.onload = function() {
                var modal = document.getElementById("modalEditar");
                modal.style.display = "block";
            }

            // Función para cerrar el modal
            function cerrarModal() {
                var modal = document.getElementById("modalEditar");
                modal.style.display = "none";
                window.location.href = 'administrar_productos.php';
            }

            // Cerrar el modal al hacer clic fuera del contenido
            window.onclick = function(event) {
                var modal = document.getElementById("modalEditar");
                if (event.target == modal) {
                    modal.style.display = "none";
                    window.location.href = 'administrar_productos.php';
                }
            }
        </script>
    <?php } ?>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>

</body>
</html>
