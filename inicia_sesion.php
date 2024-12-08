<?php
// inicia_sesion.php
session_start();
include 'conexion.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Consulta para verificar las credenciales del usuario
    $stmt = $conn->prepare("SELECT id, nombre, tipo FROM Usuarios WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Usuario encontrado, iniciar sesión
        $usuario = $result->fetch_assoc();
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario'] = $usuario['nombre'];
        $_SESSION['tipo'] = $usuario['tipo'];
        $stmt->close();

        // Redirigir al usuario según su tipo
        if ($usuario['tipo'] === 'cliente') {
            header("Location: clientes.php");
            exit();
        } elseif ($usuario['tipo'] === 'administrador') {
            header("Location: administrador.php");
            exit();
        } else {
            // En caso de tipo de usuario desconocido, redirigir al inicio
            header("Location: index.php");
            exit();
        }
    } else {
        $mensaje = "Correo electrónico o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Modern Home</title>
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

    <!-- Sección de Iniciar Sesión -->
    <main>
        <section class="login-section">
            <h2>Iniciar Sesión</h2>
            <?php
            if (!empty($mensaje)) {
                echo "<p style='color:red; font-weight: bold;'>$mensaje</p>";
            }
            ?>
            <form id="login-form" method="POST" action="inicia_sesion.php">
                <div>
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Iniciar Sesión</button>
            </form>
            <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a>.</p>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>
</body>
</html>
