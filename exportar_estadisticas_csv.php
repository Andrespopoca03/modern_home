<?php
// exportar_estadisticas_csv.php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: inicia_sesion.php");
    exit();
}

// Función para obtener todas las estadísticas necesarias
function getEstadisticasCSV($conn) {
    // Ventas por mes
    $query_ventas_mes = "
        SELECT 
            DATE_FORMAT(fecha, '%Y-%m') AS mes,
            SUM(total) AS total_ventas
        FROM 
            compras
        GROUP BY 
            DATE_FORMAT(fecha, '%Y-%m')
        ORDER BY 
            mes ASC
    ";
    $result_ventas_mes = mysqli_query($conn, $query_ventas_mes);
    $ventas_mes = [];
    while ($row = mysqli_fetch_assoc($result_ventas_mes)) {
        $ventas_mes[] = $row;
    }

    // Ventas por producto
    $query_ventas_producto = "
        SELECT 
            p.nombre AS producto,
            SUM(c.total) AS total_ventas
        FROM 
            compras c
        JOIN 
            facturas f ON c.id = f.compra_id
        JOIN 
            productos p ON f.detalles LIKE CONCAT('%', p.nombre, '%')
        GROUP BY 
            p.nombre
        ORDER BY 
            total_ventas DESC
    ";
    $result_ventas_producto = mysqli_query($conn, $query_ventas_producto);
    $ventas_producto = [];
    while ($row = mysqli_fetch_assoc($result_ventas_producto)) {
        $ventas_producto[] = $row;
    }

    // Ventas por usuario
    $query_ventas_usuario = "
        SELECT 
            u.nombre AS usuario,
            SUM(c.total) AS total_ventas
        FROM 
            compras c
        JOIN 
            usuarios u ON c.usuario_id = u.id
        GROUP BY 
            u.nombre
        ORDER BY 
            total_ventas DESC
    ";
    $result_ventas_usuario = mysqli_query($conn, $query_ventas_usuario);
    $ventas_usuario = [];
    while ($row = mysqli_fetch_assoc($result_ventas_usuario)) {
        $ventas_usuario[] = $row;
    }

    return [
        'ventas_mes' => $ventas_mes,
        'ventas_producto' => $ventas_producto,
        'ventas_usuario' => $ventas_usuario
    ];
}

// Obtener todas las estadísticas
$estadisticas = getEstadisticasCSV($conn);

// Configurar las cabeceras para la descarga
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=estadisticas_ventas.csv');

// Crear un archivo de salida
$output = fopen('php://output', 'w');

// Escribir la sección de Ventas por Mes
fputcsv($output, ['Ventas por Mes']);
fputcsv($output, ['Mes', 'Total Ventas (USD)']);
foreach ($estadisticas['ventas_mes'] as $vm) {
    fputcsv($output, [$vm['mes'], $vm['total_ventas']]);
}
fputcsv($output, []); // Línea en blanco

// Escribir la sección de Ventas por Producto
fputcsv($output, ['Ventas por Producto']);
fputcsv($output, ['Producto', 'Total Ventas (USD)']);
foreach ($estadisticas['ventas_producto'] as $vp) {
    fputcsv($output, [$vp['producto'], $vp['total_ventas']]);
}
fputcsv($output, []); // Línea en blanco

// Escribir la sección de Ventas por Usuario
fputcsv($output, ['Ventas por Usuario']);
fputcsv($output, ['Usuario', 'Total Ventas (USD)']);
foreach ($estadisticas['ventas_usuario'] as $vu) {
    fputcsv($output, [$vu['usuario'], $vu['total_ventas']]);
}

fclose($output);
exit();
?>
