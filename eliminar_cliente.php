<?php
// eliminar_cliente.php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: inicia_sesion.php");
    exit();
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID no proporcionado.";
    exit();
}

$id = intval($_GET['id']);

// Eliminar el cliente
$query = "DELETE FROM usuarios WHERE id = $id AND tipo = 'cliente'";
if (mysqli_query($conn, $query)) {
    header("Location: administrar_clientes.php");
    exit();
} else {
    echo "Error al eliminar el cliente: " . mysqli_error($conn);
}
?>
