<?php
// administrador.php
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

// Obtener datos de ventas
$ventas = getVentasPorMes($conn);
$labels = [];
$datos = [];
foreach ($ventas as $venta) {
    $labels[] = $venta['mes'];
    $datos[] = $venta['total_ventas'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Modern Home</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Incluir Chart.js desde CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Incluir una biblioteca opcional para descargar el gráfico como imagen -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <style>
        /* Estilos específicos para el dashboard de administrador */
        .admin-dashboard {
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

        .admin-dashboard h2 {
            color: #333333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .admin-dashboard .dashboard-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 40px;
        }

        .admin-dashboard .dashboard-buttons a.btn {
            width: 200px;
            text-align: center;
            padding: 15px 0;
            background-color: #007BFF;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }

        .admin-dashboard .dashboard-buttons a.btn:hover {
            background-color: #0056b3;
        }

        /* Estilos para el gráfico */
        .grafico-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
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

        /* Responsividad */
        @media (max-width: 768px) {
            .dashboard-buttons a.btn {
                width: 100%;
            }

            .grafico-container {
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
                <li><a href="cerrar_sesion.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <!-- Dashboard de Administrador -->
    <main>
        <section class="admin-dashboard">
            <h2>Panel de Administración</h2>
            <div class="dashboard-buttons">
                <a href="administrar_productos.php" class="btn">Gestionar Productos</a>
                <a href="administrar_clientes.php" class="btn">Gestionar Clientes</a>
                <a href="estadisticas.php" class="btn">Estadísticas de Ventas</a>
            </div>

            <!-- Contenedor del Gráfico -->
            <div class="grafico-container">
                <h3>Ventas Mensuales</h3>
                <canvas id="ventasChart"></canvas>
                <button id="downloadChart" class="download-btn">Descargar Gráfico</button>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>

    <!-- Script para el gráfico -->
    <script>
        // Obtener los datos pasados desde PHP
        const labels = <?php echo json_encode($labels); ?>;
        const datos = <?php echo json_encode($datos); ?>;

        // Configurar el gráfico
        const ctx = document.getElementById('ventasChart').getContext('2d');
        const ventasChart = new Chart(ctx, {
            type: 'bar', // Tipo de gráfico: barra
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

        // Función para descargar el gráfico como imagen
        document.getElementById('downloadChart').addEventListener('click', function() {
            const link = document.createElement('a');
            link.href = ventasChart.toBase64Image();
            link.download = 'ventas_mensuales.png';
            link.click();
        });
    </script>

</body>
</html>
