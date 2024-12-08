<?php
// agregar_cliente.php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: inicia_sesion.php");
    exit();
}

// Verificar si se recibieron los datos
if (!isset($_POST['nombre'], $_POST['email'], $_POST['password'], $_POST['rfc'])) {
    echo "Datos incompletos.";
    exit();
}

$nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash de la contraseña
$rfc = mysqli_real_escape_string($conn, $_POST['rfc']);

// Insertar el nuevo cliente
$query = "INSERT INTO usuarios (nombre, email, password, rfc, tipo) VALUES ('$nombre', '$email', '$password', '$rfc', 'cliente')";
if (mysqli_query($conn, $query)) {
    header("Location: administrar_clientes.php");
    exit();
} else {
    echo "Error al agregar el cliente: " . mysqli_error($conn);
}
?>
