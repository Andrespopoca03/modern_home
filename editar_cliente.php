<?php
// editar_cliente.php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: inicia_sesion.php");
    exit();
}

// Verificar si se recibieron los datos
if (!isset($_POST['id'], $_POST['nombre'], $_POST['email'], $_POST['rfc'])) {
    echo "Datos incompletos.";
    exit();
}

$id = intval($_POST['id']);
$nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$rfc = mysqli_real_escape_string($conn, $_POST['rfc']);

// Actualizar el cliente
$query = "UPDATE usuarios SET nombre = '$nombre', email = '$email', rfc = '$rfc' WHERE id = $id AND tipo = 'cliente'";
if (mysqli_query($conn, $query)) {
    header("Location: administrar_clientes.php");
    exit();
} else {
    echo "Error al actualizar el cliente: " . mysqli_error($conn);
}
?>
