<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es un cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'cliente') {
    header("Location: inicia_sesion.php");
    exit();
}

// Obtener el ID de la compra desde la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: clientes.php");
    exit();
}

$compra_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'];
$mensaje = "";

// Inicializar variables
$compra = null;
$factura = null;

// Obtener la información de la compra y del usuario (cliente)
$stmt = $conn->prepare("SELECT c.*, u.nombre AS cliente_nombre, u.rfc AS cliente_rfc FROM Compras c JOIN Usuarios u ON c.usuario_id = u.id WHERE c.id = ? AND u.id = ?");
$stmt->bind_param("ii", $compra_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $mensaje = "Compra no encontrada o no tienes permiso para verla.";
} else {
    $compra = $result->fetch_assoc();

    // Obtener los detalles de la factura
    $stmt = $conn->prepare("SELECT detalles FROM Facturas WHERE compra_id = ?");
    $stmt->bind_param("i", $compra_id);
    $stmt->execute();
    $result_factura = $stmt->get_result();

    if ($result_factura->num_rows > 0) {
        $factura = $result_factura->fetch_assoc();
    } else {
        $mensaje = "Detalles de la factura no encontrados.";
    }
}
$stmt->close();

// Función para obtener detalles de productos
function obtenerDetallesProductos($detalles) {
    // Asumiendo que 'detalles' tiene formato "Producto xCantidad, Producto xCantidad, ..."
    $productos = [];
    $items = explode(",", $detalles);
    foreach ($items as $item) {
        $item = trim($item);
        if (preg_match('/^(.*?)\sx(\d+)$/', $item, $matches)) {
            $nombre = trim($matches[1]);
            $cantidad = intval($matches[2]);
            $productos[] = ['nombre' => $nombre, 'cantidad' => $cantidad];
        }
    }
    return $productos;
}

// Si la factura y la compra están disponibles, generamos la visualización
if ($compra !== null && $factura !== null) {
    // Obtener detalles de productos
    $productos_detalles = obtenerDetallesProductos($factura['detalles']);

    // Inicializar el total
    $total_calculado = 0;
    $productos_detalles_final = [];

    foreach ($productos_detalles as $producto) {
        $nombre = $producto['nombre'];
        $cantidad = $producto['cantidad'];

        // Obtener el precio del producto
        $stmt = $conn->prepare("SELECT precio FROM productos WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $prod = $result->fetch_assoc();
            $precio = $prod['precio'];
            $subtotal = $precio * $cantidad;
            $total_calculado += $subtotal;
            $productos_detalles_final[] = [
                'nombre' => $nombre,
                'cantidad' => $cantidad,
                'precio' => $precio,
                'subtotal' => $subtotal
            ];
        } else {
            $productos_detalles_final[] = [
                'nombre' => $nombre,
                'cantidad' => $cantidad,
                'precio' => 'N/A',
                'subtotal' => 'N/A'
            ];
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles de Compra - Modern Home</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Asegúrate de no agregar estilos adicionales aquí -->
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

    <!-- Sección de Detalles de Compra -->
    <main>
        <section class="carrito-section">
            <h2>Detalles de la Compra</h2>
            <?php
            if (!empty($mensaje)) {
                echo "<p style='color: red;'>$mensaje</p>";
            } elseif ($compra !== null && $factura !== null) {
            ?>
                <div class="facturacion-card" id="factura">
                    <h3>Factura de Compra ID: <?= htmlspecialchars($compra['id']) ?></h3>
                    <div class="facturacion-content">
                        <!-- Información del Emisor (Modern Home) -->
                        <div class="facturacion-info">
                            <h4>Emisor: Modern Home</h4>
                            <p>Dirección: Ejemplo #123, Colonia Centro, Cuernavaca, Morelos</p>
                            <p>RFC: MODHOM1234567</p>
                        </div>
                        
                        <!-- Información del Receptor (Cliente) -->
                        <div class="facturacion-info">
                            <h4>Receptor: <?= htmlspecialchars($compra['cliente_nombre']) ?></h4>
                            <p>RFC: <?= htmlspecialchars($compra['cliente_rfc']) ?></p>
                        </div>
                        
                        <!-- Información de la compra -->
                        <div class="facturacion-info">
                            <ul>
                                <li>Fecha: <?= date("d/m/Y H:i:s", strtotime($compra['fecha'])) ?></li>
                                <li>Método de Pago: <?= htmlspecialchars($compra['metodo_pago']) ?></li>
                            </ul>
                        </div>
                        
                        <!-- Detalles de los productos -->
                        <div class="facturacion-info">
                            <h4>Detalles de Productos:</h4>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productos_detalles_final as $prod): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($prod['nombre']) ?></td>
                                            <td><?= htmlspecialchars($prod['cantidad']) ?></td>
                                            <td><?= is_numeric($prod['precio']) ? "$" . number_format($prod['precio'], 2, ',', '.') : $prod['precio'] ?></td>
                                            <td><?= is_numeric($prod['subtotal']) ? "$" . number_format($prod['subtotal'], 2, ',', '.') : $prod['subtotal'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <p><strong>Total Calculado: $<?= number_format($total_calculado, 2, ',', '.') ?></strong></p>
                        </div>
                    </div>
                    
                    <!-- Código QR para acceder a la factura -->
                    <div class="qr-descargar">
                        <p>Escanea el siguiente QR para acceder y descargar tu factura:</p>
                        <div id="qrcode-descargar" style="margin-top: 10px;"></div>
                    </div>
                </div>
                
                <!-- Enlace para regresar -->
                <a href="clientes.php" class="btn">Volver a Mis Compras</a>
                
                <!-- Script para generar el QR -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                <script>
                    // Obtener la URL completa de la factura
                    const facturaURL = window.location.href;
    
                    // Generar el QR
                    new QRCode(document.getElementById("qrcode-descargar"), {
                        text: facturaURL,
                        width: 128,
                        height: 128,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H
                    });
                </script>
            <?php
            } else {
                echo "<p style='color: red;'>Ocurrió un error al cargar los detalles de la compra.</p>";
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
