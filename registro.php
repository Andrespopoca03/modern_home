<?php
// registro.php
session_start();
include 'conexion.php';

$mensaje = "";

// Manejar la solicitud POST (registro de usuario)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y sanitizar los datos enviados desde el formulario
    $nombre = trim($_POST['nombre']) ?? '';
    $email = trim($_POST['email']) ?? '';
    $password = trim($_POST['password']) ?? '';
    $rfc = trim($_POST['rfc']) ?? '';

    // Validar que todos los campos estén llenos
    if (empty($nombre) || empty($email) || empty($password) || empty($rfc)) {
        $mensaje = "Por favor, completa todos los campos.";
    } else {
        // Verificar si el correo electrónico ya está registrado
        $stmt = $conn->prepare("SELECT id FROM Usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $mensaje = "El correo electrónico ya está registrado.";
        } else {
            $stmt->close();

            // Verificar si el RFC ya está registrado
            $stmt = $conn->prepare("SELECT id FROM Usuarios WHERE rfc = ?");
            $stmt->bind_param("s", $rfc);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $mensaje = "El RFC ya está registrado.";
            } else {
                $stmt->close();

                // Insertar el nuevo usuario como 'cliente'
                $tipo = 'cliente';
                $stmt = $conn->prepare("INSERT INTO Usuarios (nombre, email, password, tipo, rfc) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $nombre, $email, $password, $tipo, $rfc);

                if ($stmt->execute()) {
                    $mensaje = "¡Registro exitoso! Ahora puedes <a href='inicia_sesion.php'>iniciar sesión</a>.";
                } else {
                    $mensaje = "Error al registrar el usuario: " . $stmt->error;
                }
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Modern Home</title>
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

    <!-- Sección de Registro -->
    <main>
        <section class="register-section">
            <h2>Registro de Cliente</h2>
            <?php
            if (!empty($mensaje)) {
                echo "<p style='color: red; font-weight: bold;'>$mensaje</p>";
            }
            ?>
            <form id="register-form" method="POST" action="registro.php">
                <div>
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div>
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div>
                    <label for="rfc">RFC:</label>
                    <input type="text" id="rfc" name="rfc" maxlength="13" required>
                </div>
                <button type="submit" class="btn">Registrarse</button>
            </form>
            <p>¿Ya tienes una cuenta? <a href="inicia_sesion.php">Inicia Sesión aquí</a>.</p>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>
</body>
</html>
