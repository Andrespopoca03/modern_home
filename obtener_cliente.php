<?php
// obtener_cliente.php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit();
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
    exit();
}

$id = intval($_GET['id']);

// Consultar el cliente
$query = "SELECT id, nombre, email, rfc FROM usuarios WHERE id = $id AND tipo = 'cliente'";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['success' => true, 'cliente' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cliente no encontrado.']);
}
?>
