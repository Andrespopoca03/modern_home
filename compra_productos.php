<?php
// compra_productos.php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: inicia_sesion.php");
    exit();
}

$mensaje = "";
$factura = null;

// Manejar la solicitud POST (procesar la compra)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos enviados desde el formulario
    $cart_json = $_POST['cart'] ?? '';
    $metodo_pago = $_POST['metodo-pago'] ?? '';
    $nombre_tarjeta = $_POST['nombre-tarjeta'] ?? '';
    $numero_tarjeta = $_POST['numero-tarjeta'] ?? '';
    $expiracion = $_POST['expiracion'] ?? '';
    $cvv = $_POST['cvv'] ?? '';

    // Validar que se hayan recibido los datos del carrito
    if (empty($cart_json)) {
        $mensaje = "El carrito está vacío.";
    } elseif (empty($metodo_pago)) {
        $mensaje = "Por favor, selecciona un método de pago.";
    } else {
        // Decodificar el carrito JSON
        $cart = json_decode($cart_json, true);
        if ($cart === null) {
            $mensaje = "Error al procesar el carrito. Intenta nuevamente.";
        } else {
            // Calcular el total de la compra y crear detalles de la factura
            $total = 0;
            $detalles = "";
            $usuario_id = $_SESSION['usuario_id'];

            foreach ($cart as $item) {
                $productoId = intval($item['productoId']);
                $cantidad = isset($item['cantidad']) ? intval($item['cantidad']) : 1;

                // Obtener detalles del producto desde la base de datos
                $stmt = $conn->prepare("SELECT nombre, precio FROM Productos WHERE id = ?");
                $stmt->bind_param("i", $productoId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($producto = $result->fetch_assoc()) {
                    $nombre = $producto['nombre'];
                    $precio = $producto['precio'];
                    $subtotal = $precio * $cantidad;
                    $total += $subtotal;
                    $detalles .= htmlspecialchars($nombre) . " x" . $cantidad . ", ";
                } else {
                    $mensaje = "Producto con ID $productoId no encontrado.";
                    break;
                }
                $stmt->close();
            }

            $detalles = rtrim($detalles, ", ");

            if (empty($mensaje)) {
                // Insertar la compra en la tabla `Compras` con metodo_pago
                $stmt = $conn->prepare("INSERT INTO Compras (usuario_id, metodo_pago, total) VALUES (?, ?, ?)");
                $stmt->bind_param("isd", $usuario_id, $metodo_pago, $total);
                if ($stmt->execute()) {
                    $compra_id = $stmt->insert_id;
                    $stmt->close();

                    // Insertar la factura en la tabla `Facturas`
                    $stmt = $conn->prepare("INSERT INTO Facturas (compra_id, detalles) VALUES (?, ?)");
                    $stmt->bind_param("is", $compra_id, $detalles);
                    if ($stmt->execute()) {
                        $factura = [
                            'compra_id' => $compra_id,
                            'detalles' => $detalles,
                            'total' => number_format($total, 2, ',', '.'),
                            'fecha' => date("d/m/Y H:i:s")
                        ];
                        $stmt->close();

                        // Limpiar el carrito en el cliente (instrucciones en JavaScript)
                        $mensaje = "¡Compra exitosa! Tu factura se muestra a continuación.";

                        // Limpiar el carrito en el navegador
                        echo "<script>
                            localStorage.removeItem('carrito');
                        </script>";
                    } else {
                        $mensaje = "Error al crear la factura: " . $stmt->error;
                    }
                } else {
                    $mensaje = "Error al registrar la compra: " . $stmt->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Compra - Modern Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Header y Navbar -->
    <header>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="cerrar_sesion.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <!-- Sección de Finalizar Compra -->
    <main>
        <section class="carrito-section">
            <h2>Finalizar Compra</h2>
            <?php
            if (!empty($mensaje)) {
                echo "<p style='color: red; font-weight: bold;'>$mensaje</p>";
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST" && $factura !== null) {
                // Mostrar la factura
                echo "<div class='facturacion-card'>";
                echo "<h3>Factura de Compra</h3>";
                echo "<div class='facturacion-content'>";
                echo "<div class='facturacion-info'>";
                echo "<h4>Modern Home</h4>";
                echo "<ul>";
                echo "<li>Compra ID: " . htmlspecialchars($factura['compra_id']) . "</li>";
                echo "<li>Fecha: " . htmlspecialchars($factura['fecha']) . "</li>";
                echo "<li>Detalles: " . htmlspecialchars($factura['detalles']) . "</li>";
                echo "</ul>";
                echo "<p>Total: $" . htmlspecialchars($factura['total']) . "</p>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            } else {
                // Mostrar el resumen del carrito y el formulario de pago
            ?>
                <div class="facturacion-card">
                    <h3>Facturación</h3>
                    <div class="facturacion-content">
                        <div class="facturacion-info">
                            <h4>Modern Home</h4>
                            <ul id="carrito-lista">
                                <!-- Productos seleccionados se llenarán aquí por JavaScript -->
                            </ul>
                            <p>Total: $<span id="total">0</span></p>
                        </div>
                        <div class="facturacion-metodo">
                            <h4>Método de Pago</h4>
                            <form id="payment-form" method="POST" action="compra_productos.php">
                                <label>
                                    <input type="radio" name="metodo-pago" value="tarjeta" required>
                                    Tarjeta de Crédito/Débito
                                </label><br>
                                <label>
                                    <input type="radio" name="metodo-pago" value="paypal">
                                    PayPal
                                </label><br>
                                <label>
                                    <input type="radio" name="metodo-pago" value="efectivo">
                                    Efectivo
                                </label>
                                
                                <!-- Campos de Tarjeta de Crédito/Débito -->
                                <div id="tarjeta-pago" style="display: none; margin-top: 20px;">
                                    <h4>Detalles de Tarjeta</h4>
                                    <div>
                                        <label for="nombre-tarjeta">Nombre en la Tarjeta:</label>
                                        <input type="text" id="nombre-tarjeta" name="nombre-tarjeta">
                                    </div>
                                    <div>
                                        <label for="numero-tarjeta">Número de Tarjeta:</label>
                                        <input type="text" id="numero-tarjeta" name="numero-tarjeta" maxlength="19" placeholder="1234 5678 9012 3456">
                                    </div>
                                    <div style="display: flex; gap: 20px;">
                                        <div>
                                            <label for="expiracion">Expiración:</label>
                                            <input type="text" id="expiracion" name="expiracion" maxlength="5" placeholder="MM/AA">
                                        </div>
                                        <div>
                                            <label for="cvv">CVV:</label>
                                            <input type="text" id="cvv" name="cvv" maxlength="3">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Botón para Procesar Pago -->
                                <input type="hidden" name="cart" id="cart-data">
                                <button type="submit" class="btn">Procesar Pago</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Opcional: Formularios adicionales para PayPal y Efectivo -->
                <div id="paypal-form" style="display: none; margin-top: 40px;">
                    <h3>Pago con PayPal</h3>
                    <p>Serás redirigido a PayPal para completar tu pago.</p>
                    <button id="btn-paypal" class="btn">Pagar con PayPal</button>
                </div>

                <div id="qr-efectivo" style="display: none; margin-top: 40px;">
                    <h3>Pago en Efectivo</h3>
                    <p>Escanea el siguiente código QR para completar tu pago en efectivo.</p>
                    <div id="qrcode-efectivo"></div>
                </div>
            <?php } ?>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>

    <!-- Scripts -->
    <!-- Incluir la librería QRCode.js si es necesario -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
    // Función para actualizar el resumen del carrito en la página
    function actualizarCarritoCompra() {
        const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
        const listaCarrito = document.getElementById('carrito-lista');
        const total = document.getElementById('total');
        listaCarrito.innerHTML = '';
        let sumaTotal = 0;

        carrito.forEach((item) => {
            const li = document.createElement('li');
            li.textContent = `${item.nombre} x${item.cantidad} - $${(item.precio * item.cantidad).toLocaleString('es-MX')}`;
            listaCarrito.appendChild(li);
            sumaTotal += item.precio * item.cantidad;
        });

        total.textContent = sumaTotal.toLocaleString('es-MX');
    }

    // Función para manejar la selección del método de pago
    function manejarMetodoPago() {
        const radios = document.getElementsByName('metodo-pago');
        const tarjetaPago = document.getElementById('tarjeta-pago');
        const paypalForm = document.getElementById('paypal-form');
        const qrEfectivo = document.getElementById('qr-efectivo');

        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                // Ocultar todos los formularios inicialmente
                tarjetaPago.style.display = 'none';
                paypalForm.style.display = 'none';
                qrEfectivo.style.display = 'none';

                if (radio.value === 'tarjeta') {
                    tarjetaPago.style.display = 'block';
                } else if (radio.value === 'paypal') {
                    paypalForm.style.display = 'block';
                } else if (radio.value === 'efectivo') {
                    qrEfectivo.style.display = 'block';
                    generarQREfectivo();
                }
            });
        });
    }

    // Función para generar el QR para pago en efectivo
    function generarQREfectivo() {
        const total = document.getElementById('total').textContent;
        const qrcodeEfectivo = document.getElementById("qrcode-efectivo");
        qrcodeEfectivo.innerHTML = ""; // Limpiar QR existente si hay alguno

        try {
            new QRCode(qrcodeEfectivo, {
                text: `Pago Efectivo: $${total}`,
                width: 128,
                height: 128,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        } catch (error) {
            console.error('Error al generar el QR:', error);
        }
    }

    // Manejar el envío del formulario de pago
    function manejarEnvioPago() {
        const formPago = document.getElementById('payment-form');
        formPago.addEventListener('submit', (e) => {
            const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
            if (carrito.length === 0) {
                e.preventDefault();
                alert("Tu carrito está vacío.");
                return;
            }
            // Agregar los datos del carrito al campo oculto
            document.getElementById('cart-data').value = JSON.stringify(carrito);
        });
    }

    // Función para actualizar el carrito al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
        actualizarCarritoCompra();
        manejarMetodoPago();
        manejarEnvioPago();
    });
    </script>

</body>
</html>
