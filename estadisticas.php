<?php
// estadisticas.php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: inicia_sesion.php");
    exit();
}

// Función para obtener datos de ventas por mes
function getVentasPorMes($conn) {
    $query = "
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
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Función para obtener ventas por producto
function getVentasPorProducto($conn) {
    $query = "
        SELECT 
            p.nombre AS producto,
            SUM(c.total) AS total_ventas
        FROM 
            compras c
        JOIN 
            facturas f ON c.id = f.compra_id
        JOIN 
            productos p ON FIND_IN_SET(p.id, REPLACE(f.detalles, ' ', '')) > 0
        GROUP BY 
            p.nombre
        ORDER BY 
            total_ventas DESC
    ";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Función para obtener ventas por usuario
function getVentasPorUsuario($conn) {
    $query = "
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
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Función para obtener ventas por producto (Pie Chart)
function getVentasPorProductoPie($conn) {
    $query = "
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
        LIMIT 10
    ";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Obtener datos de ventas
$ventas = getVentasPorMes($conn);
$labels = [];
$datos = [];
foreach ($ventas as $venta) {
    $labels[] = $venta['mes'];
    $datos[] = $venta['total_ventas'];
}

// Obtener ventas por producto
$ventas_productos = getVentasPorProducto($conn);
$labels_productos = [];
$datos_productos = [];
foreach ($ventas_productos as $vp) {
    $labels_productos[] = $vp['producto'];
    $datos_productos[] = $vp['total_ventas'];
}

// Obtener ventas por usuario
$ventas_usuarios = getVentasPorUsuario($conn);
$labels_usuarios = [];
$datos_usuarios = [];
foreach ($ventas_usuarios as $vu) {
    $labels_usuarios[] = $vu['usuario'];
    $datos_usuarios[] = $vu['total_ventas'];
}

// Obtener ventas por producto para gráfico de pastel
$ventas_productos_pie = getVentasPorProductoPie($conn);
$labels_productos_pie = [];
$datos_productos_pie = [];
foreach ($ventas_productos_pie as $vp_pie) {
    $labels_productos_pie[] = $vp_pie['producto'];
    $datos_productos_pie[] = $vp_pie['total_ventas'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas y Reportes - Modern Home</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Incluir Chart.js desde CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Incluir Chart.js Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <!-- Incluir DataTables CSS y JS (Opcional) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <!-- DataTables Spanish Language -->
    <script src="https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"></script>
    <style>
        /* Estilos específicos para estadisticas.php */
        .estadisticas-dashboard {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            background-color: #ffffff;
            max-width: 1200px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.5s ease-in-out;
        }

        .estadisticas-dashboard h2 {
            color: #333333;
            margin-bottom: 30px;
            font-size: 28px;
            text-align: center;
        }

        .grafico-container {
            width: 100%;
            max-width: 1000px;
            margin: 20px 0;
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .grafico-container h3 {
            text-align: center;
            color: #333333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        /* Botón para descargar el gráfico */
        .download-btn {
            display: block;
            margin: 20px auto 0 auto;
            padding: 10px 20px;
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 16px;
            cursor: pointer;
            width: fit-content;
        }

        .download-btn:hover {
            background-color: #1e7e34;
        }

        /* Estilos para la tabla de estadísticas */
        .estadisticas-table-container {
            width: 100%;
            max-width: 1000px;
            margin: 20px 0;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Responsividad */
        @media (max-width: 768px) {
            .grafico-container, .estadisticas-table-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Header y Navbar -->
    <header>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="clientes.php">Clientes</a></li>
                <li><a href="administrador.php">Administrador</a></li>
                <li><a href="estadisticas.php" >Estadísticas</a></li>
                <li><a href="cerrar_sesion.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <!-- Sección Estadísticas y Reportes -->
    <main>
        <section class="estadisticas-dashboard">
            <h2>Estadísticas y Reportes de Ventas</h2>

            <!-- Gráfico de Ventas Mensuales (Bar Chart) -->
            <div class="grafico-container">
                <h3>Ventas Mensuales</h3>
                <canvas id="ventasChart"></canvas>
                <button id="downloadVentasChart" class="download-btn">Descargar Gráfico</button>
            </div>

            <!-- Gráfico de Tendencia de Ventas (Line Chart) -->
            <div class="grafico-container">
                <h3>Tendencia de Ventas</h3>
                <canvas id="tendenciaChart"></canvas>
                <button id="downloadTendenciaChart" class="download-btn">Descargar Gráfico</button>
            </div>

            <!-- Gráfico de Distribución de Ventas por Producto (Pie Chart) -->
            <div class="grafico-container">
                <h3>Distribución de Ventas por Producto</h3>
                <canvas id="productosPieChart"></canvas>
                <button id="downloadProductosPieChart" class="download-btn">Descargar Gráfico</button>
            </div>

            <!-- Tabla de Ventas por Usuario -->
            <div class="estadisticas-table-container">
                <h3>Ventas por Usuario</h3>
                <table id="ventasUsuariosTable" class="display">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Total Ventas (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas_usuarios as $vu): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($vu['usuario']); ?></td>
                                <td><?php echo '$' . number_format($vu['total_ventas'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Botón para Exportar Datos a CSV -->
            <button id="exportCsv" class="download-btn">Exportar Datos CSV</button>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>

    <!-- Scripts para los Gráficos y Funcionalidades -->
    <script>
        // Obtener los datos pasados desde PHP
        const labels = <?php echo json_encode($labels); ?>;
        const datos = <?php echo json_encode($datos); ?>;

        // Configurar el gráfico de Ventas Mensuales (Bar Chart)
        const ctxVentas = document.getElementById('ventasChart').getContext('2d');
        const ventasChart = new Chart(ctxVentas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ventas en USD',
                    data: datos,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    hoverBackgroundColor: 'rgba(54, 162, 235, 0.8)',
                    hoverBorderColor: 'rgba(54, 162, 235, 1)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Ventas Mensuales de 2024',
                        font: {
                            size: 18
                        }
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += '$' + context.parsed.y.toLocaleString();
                                }
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        formatter: function(value) {
                            return '$' + value.toLocaleString();
                        },
                        color: '#333',
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad de Ventas (USD)',
                            font: {
                                size: 16
                            }
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Meses',
                            font: {
                                size: 16
                            }
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Configurar el gráfico de Tendencia de Ventas (Line Chart)
        const ctxTendencia = document.getElementById('tendenciaChart').getContext('2d');
        const tendenciaChart = new Chart(ctxTendencia, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ventas en USD',
                    data: datos,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(255, 99, 132, 1)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Tendencia de Ventas de 2024',
                        font: {
                            size: 18
                        }
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += '$' + context.parsed.y.toLocaleString();
                                }
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        display: false // Opcional: desactivar etiquetas de datos
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad de Ventas (USD)',
                            font: {
                                size: 16
                            }
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Meses',
                            font: {
                                size: 16
                            }
                        }
                    }
                }
            }
        });

        // Obtener datos de ventas por producto para gráfico de pastel
        const labelsProductosPie = <?php echo json_encode($labels_productos_pie); ?>;
        const datosProductosPie = <?php echo json_encode($datos_productos_pie); ?>;

        // Configurar el gráfico de Distribución de Ventas por Producto (Pie Chart)
        const ctxProductosPie = document.getElementById('productosPieChart').getContext('2d');
        const productosPieChart = new Chart(ctxProductosPie, {
            type: 'pie',
            data: {
                labels: labelsProductosPie,
                datasets: [{
                    label: 'Ventas en USD',
                    data: datosProductosPie,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(199, 199, 199, 0.6)',
                        'rgba(83, 102, 255, 0.6)',
                        'rgba(255, 102, 255, 0.6)',
                        'rgba(255, 102, 0, 0.6)'
                    ],
                    borderColor: [
                        'rgba(255,99,132,1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(255, 102, 255, 1)',
                        'rgba(255, 102, 0, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Ventas por Producto (Top 10)',
                        font: {
                            size: 18
                        }
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += '$' + context.parsed.toLocaleString();
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Configurar DataTables para la tabla de Ventas por Usuario
        $(document).ready(function() {
            $('#ventasUsuariosTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
                },
                "paging": true,
                "searching": true,
                "ordering": true,
                "order": [[1, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": [] }
                ]
            });
        });

        // Funciones para descargar los gráficos como imágenes
        document.getElementById('downloadVentasChart').addEventListener('click', function() {
            const link = document.createElement('a');
            link.href = ventasChart.toBase64Image();
            link.download = 'ventas_mensuales.png';
            link.click();
        });

        document.getElementById('downloadTendenciaChart').addEventListener('click', function() {
            const link = document.createElement('a');
            link.href = tendenciaChart.toBase64Image();
            link.download = 'tendencia_ventas.png';
            link.click();
        });

        document.getElementById('downloadProductosPieChart').addEventListener('click', function() {
            const link = document.createElement('a');
            link.href = productosPieChart.toBase64Image();
            link.download = 'distribucion_ventas_productos.png';
            link.click();
        });

        // Función para exportar datos a CSV
        document.getElementById('exportCsv').addEventListener('click', function() {
            window.location.href = 'exportar_estadisticas_csv.php';
        });
    </script>

</body>
</html>
