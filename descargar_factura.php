<?php
// descargar_factura.php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es un cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'cliente') {
    exit('Acceso no autorizado.');
}

// Obtener el ID de la compra desde la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    exit('ID de factura no especificado.');
}

$compra_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'];

// Verificar si el usuario tiene permiso para acceder a esta factura
$stmt = $conn->prepare("SELECT c.id FROM Compras c WHERE c.id = ? AND c.usuario_id = ?");
$stmt->bind_param("ii", $compra_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exit('No tienes permiso para descargar esta factura.');
}

// Ruta de la imagen
$image_filename = 'factura_' . $compra_id . '.png';
$image_path = __DIR__ . '/facturas/' . $image_filename;

// Verificar si el archivo existe
if (!file_exists($image_path)) {
    exit('Factura no encontrada.');
}

// Enviar los encabezados para la descarga
header('Content-Description: File Transfer');
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="' . basename($image_filename) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($image_path));

// Limpiar el buffer de salida
ob_clean();
flush();

// Leer el archivo y enviarlo al navegador
readfile($image_path);
exit;
?>
