<?php
// conexion.php

$servername = "localhost";
$username = "root";     // Usuario por defecto en XAMPP
$password = "";         // Contraseña por defecto en XAMPP (vacía)
$dbname = "modern_home";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
