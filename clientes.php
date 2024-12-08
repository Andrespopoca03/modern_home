<?php
// clientes.php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es un cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'cliente') {
    header("Location: inicia_sesion.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = "";

// Obtener todas las compras del cliente desde la tabla `Compras`
$stmt = $conn->prepare("SELECT id, fecha, total FROM Compras WHERE usuario_id = ? ORDER BY fecha DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$compras = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $compras[] = $row;
    }
} else {
    $mensaje = "No has realizado ninguna compra aún.";
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Compras - Modern Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Header y Navbar -->
    <header>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="clientes.php">Mis Compras</a></li>
                <li><a href="cerrar_sesion.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <!-- Sección de Compras -->
    <main>
        <section class="carrito-section">
            <h2>Mis Compras</h2>
            <?php
            if (!empty($mensaje)) {
                echo "<p>$mensaje</p>";
            } else {
                echo "<table border='1' cellpadding='10' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
                echo "<tr>";
                echo "<th>ID de Compra</th>";
                echo "<th>Fecha</th>";
                echo "<th>Total</th>";
                echo "<th>Acciones</th>";
                echo "</tr>";
                foreach ($compras as $compra) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($compra['id']) . "</td>";
                    echo "<td>" . date("d/m/Y H:i:s", strtotime($compra['fecha'])) . "</td>";
                    echo "<td>$" . number_format($compra['total'], 2, ',', '.') . "</td>";
                    echo "<td><a href='detalles_compra.php?id=" . urlencode($compra['id']) . "' class='btn'>Ver Detalles</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            ?>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>

</body>
</html>
