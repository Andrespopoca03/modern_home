<?php
// reportes.php
session_start();
include 'conexion.php';

// Función para manejar consultas con manejo de errores
function ejecutarConsulta($conn, $query, $mensaje_error) {
    $result = mysqli_query($conn, $query);
    if (!$result) {
        // Registrar el error en el archivo de logs
        error_log($mensaje_error . ": " . mysqli_error($conn));
        // Mostrar un mensaje amigable al usuario
        echo "<div class='error'>Ha ocurrido un error al procesar la solicitud. Por favor, inténtalo de nuevo más tarde.</div>";
        // Terminar la ejecución
        exit();
    }
    return $result;
}

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: inicia_sesion.php");
    exit();
}

// Funciones para obtener datos de la base de datos

// Ventas por Mes
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
    $result = ejecutarConsulta($conn, $query, "Error en la consulta Ventas por Mes");
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Ventas por Producto (Top 10)
function getVentasPorProductoTop($conn) {
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
        LIMIT 10
    ";
    $result = ejecutarConsulta($conn, $query, "Error en la consulta Ventas por Producto Top 10");
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Ventas por Región
function getVentasPorRegion($conn) {
    $query = "
        SELECT 
            u.region AS region,
            SUM(c.total) AS total_ventas
        FROM 
            compras c
        JOIN 
            usuarios u ON c.usuario_id = u.id
        GROUP BY 
            u.region
        ORDER BY 
            total_ventas DESC
    ";
    $result = ejecutarConsulta($conn, $query, "Error en la consulta Ventas por Región");
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Ventas por Usuario (Top 10)
function getVentasPorUsuarioTop($conn) {
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
        LIMIT 10
    ";
    $result = ejecutarConsulta($conn, $query, "Error en la consulta Ventas por Usuario Top 10");
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Obtener datos para los gráficos
$ventas_mes = getVentasPorMes($conn);
$ventas_producto_top = getVentasPorProductoTop($conn);
$ventas_region = getVentasPorRegion($conn);
$ventas_usuario_top = getVentasPorUsuarioTop($conn);

// Preparar datos para JavaScript
$labels_mes = [];
$datos_mes = [];
foreach ($ventas_mes as $vm) {
    $labels_mes[] = $vm['mes'];
    $datos_mes[] = $vm['total_ventas'];
}

$labels_producto_top = [];
$datos_producto_top = [];
foreach ($ventas_producto_top as $vp) {
    $labels_producto_top[] = $vp['producto'];
    $datos_producto_top[] = $vp['total_ventas'];
}

$labels_region = [];
$datos_region = [];
foreach ($ventas_region as $vr) {
    $labels_region[] = $vr['region'];
    $datos_region[] = $vr['total_ventas'];
}

$labels_usuario_top = [];
$datos_usuario_top = [];
foreach ($ventas_usuario_top as $vu) {
    $labels_usuario_top[] = $vu['usuario'];
    $datos_usuario_top[] = $vu['total_ventas'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Modern Home</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Incluir Chart.js desde CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Incluir Chart.js Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <!-- Incluir DataTables CSS y JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <!-- DataTables Spanish Language -->
    <script src="https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"></script>
    <!-- html2pdf.js para exportar a PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        /* Estilos específicos para reportes.php */
        .reportes-dashboard {
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

        .reportes-dashboard h2 {
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
        .download-btn, .export-btn {
            display: inline-block;
            margin: 10px 5px;
            padding: 10px 20px;
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 16px;
            cursor: pointer;
        }

        .download-btn:hover, .export-btn:hover {
            background-color: #1e7e34;
        }

        /* Estilos para la tabla de Ventas por Usuario */
        .reportes-table-container {
            width: 100%;
            max-width: 1000px;
            margin: 20px 0;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para mensajes de error */
        .error {
            color: #ff0000;
            background-color: #ffe6e6;
            padding: 15px;
            border: 1px solid #ff0000;
            border-radius: 5px;
            margin: 20px;
            text-align: center;
            font-size: 18px;
        }

        /* Responsividad */
        @media (max-width: 768px) {
            .grafico-container, .reportes-table-container {
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
                <li><a href="estadisticas.php">Estadísticas</a></li>
                <li><a href="reportes.php" class="active">Reportes</a></li>
                <li><a href="cerrar_sesion.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <!-- Sección Reportes -->
    <main>
        <section class="reportes-dashboard">
            <h2>Reportes de Ventas</h2>

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
                <h3>Distribución de Ventas por Producto (Top 10)</h3>
                <canvas id="productosPieChart"></canvas>
                <button id="downloadProductosPieChart" class="download-btn">Descargar Gráfico</button>
            </div>

            <!-- Gráfico de Ventas por Región (Donut Chart) -->
            <div class="grafico-container">
                <h3>Ventas por Región</h3>
                <canvas id="ventasRegionChart"></canvas>
                <button id="downloadVentasRegionChart" class="download-btn">Descargar Gráfico</button>
            </div>

            <!-- Tabla de Ventas por Usuario -->
            <div class="reportes-table-container">
                <h3>Ventas por Usuario (Top 10)</h3>
                <table id="ventasUsuariosTable" class="display">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Total Ventas (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas_usuario_top as $vu): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($vu['usuario']); ?></td>
                                <td><?php echo '$' . number_format($vu['total_ventas'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button id="exportCsv" class="export-btn">Exportar Datos CSV</button>
                <button id="exportPdf" class="export-btn">Exportar a PDF</button>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>

    <!-- Scripts para los Gráficos y Funcionalidades -->
    <script>
        // Obtener los datos pasados desde PHP
        const labelsMes = <?php echo json_encode($labels_mes); ?>;
        const datosMes = <?php echo json_encode($datos_mes); ?>;

        const labelsProductoTop = <?php echo json_encode($labels_producto_top); ?>;
        const datosProductoTop = <?php echo json_encode($datos_producto_top); ?>;

        const labelsRegion = <?php echo json_encode($labels_region); ?>;
        const datosRegion = <?php echo json_encode($datos_region); ?>;

        const labelsUsuarioTop = <?php echo json_encode($labels_usuario_top); ?>;
        const datosUsuarioTop = <?php echo json_encode($datos_usuario_top); ?>;

        // Configurar el gráfico de Ventas Mensuales (Bar Chart)
        const ctxVentas = document.getElementById('ventasChart').getContext('2d');
        const ventasChart = new Chart(ctxVentas, {
            type: 'bar',
            data: {
                labels: labelsMes,
                datasets: [{
                    label: 'Ventas en USD',
                    data: datosMes,
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
                labels: labelsMes,
                datasets: [{
                    label: 'Ventas en USD',
                    data: datosMes,
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

        // Configurar el gráfico de Distribución de Ventas por Producto (Pie Chart)
        const ctxProductosPie = document.getElementById('productosPieChart').getContext('2d');
        const productosPieChart = new Chart(ctxProductosPie, {
            type: 'pie',
            data: {
                labels: labelsProductoTop,
                datasets: [{
                    label: 'Ventas en USD',
                    data: datosProductoTop,
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

        // Configurar el gráfico de Ventas por Región (Donut Chart)
        const ctxVentasRegion = document.getElementById('ventasRegionChart').getContext('2d');
        const ventasRegionChart = new Chart(ctxVentasRegion, {
            type: 'doughnut',
            data: {
                labels: labelsRegion,
                datasets: [{
                    label: 'Ventas en USD',
                    data: datosRegion,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(199, 199, 199, 0.6)',
                        'rgba(83, 102, 255, 0.6)',
                        'rgba(255, 102, 255, 0.6)',
                        'rgba(255, 102, 0, 0.6)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255,99,132,1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
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
                        text: 'Ventas por Región',
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

        document.getElementById('downloadVentasRegionChart').addEventListener('click', function() {
            const link = document.createElement('a');
            link.href = ventasRegionChart.toBase64Image();
            link.download = 'ventas_por_region.png';
            link.click();
        });

        // Función para exportar datos a CSV
        document.getElementById('exportCsv').addEventListener('click', function() {
            window.location.href = 'exportar_reportes_csv.php';
        });

        // Función para exportar tabla a PDF
        document.getElementById('exportPdf').addEventListener('click', function() {
            const element = document.querySelector('.reportes-table-container');
            const opt = {
                margin:       1,
                filename:     'reportes_ventas.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        });
    </script>

</body>
</html>
