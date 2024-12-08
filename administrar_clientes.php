<?php
// administrar_clientes.php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: inicia_sesion.php");
    exit();
}

// Función para obtener la lista de clientes
function getClientes($conn) {
    $query = "SELECT id, nombre, email, rfc FROM usuarios WHERE tipo = 'cliente' ORDER BY id ASC";
    $result = mysqli_query($conn, $query);
    $clientes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $clientes[] = $row;
    }
    return $clientes;
}

// Obtener la lista de clientes
$clientes = getClientes($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Clientes - Modern Home</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Incluir Chart.js y otros scripts si es necesario -->
    <style>
        /* Estilos específicos para administrar_clientes.php */
        .admin-clientes {
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

        .admin-clientes h2 {
            color: #333333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        /* Tabla de Clientes */
        .clientes-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .clientes-table th, 
        .clientes-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dddddd;
        }

        .clientes-table th {
            background-color: #f2f2f2;
            color: #333333;
        }

        .clientes-table tr:hover {
            background-color: #f9f9f9;
        }

        /* Botones de Acción */
        .clientes-table .btn {
            padding: 8px 12px;
            background-color: #007BFF;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 14px;
            margin-right: 5px;
            cursor: pointer;
            display: inline-block;
        }

        .clientes-table .btn:hover {
            background-color: #0056b3;
        }

        .clientes-table .btn-editar {
            background-color: #ffc107;
        }

        .clientes-table .btn-editar:hover {
            background-color: #e0a800;
        }

        .clientes-table .btn-eliminar {
            background-color: #dc3545;
        }

        .clientes-table .btn-eliminar:hover {
            background-color: #c82333;
        }

        /* Modal para Editar Cliente */
        .modal {
            display: none; /* Oculto por defecto */
            position: fixed; /* Fijo */
            z-index: 1001; /* Sobre otros elementos */
            left: 0;
            top: 0;
            width: 100%; /* Ancho completo */
            height: 100%; /* Alto completo */
            overflow: auto; /* Habilita scroll si es necesario */
            background-color: rgba(0,0,0,0.4); /* Negro con opacidad */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto; /* 10% desde arriba y centrado */
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px; /* Ancho máximo */
            border-radius: 10px;
            position: relative;
        }

        .close-modal {
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover,
        .close-modal:focus {
            color: #000;
            text-decoration: none;
        }

        /* Formulario de Edición */
        #editar-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        #editar-form label {
            font-size: 16px;
            color: #333333;
        }

        #editar-form input {
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size: 16px;
        }

        #editar-form button {
            padding: 12px 20px;
            background-color: #28a745;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        #editar-form button:hover {
            background-color: #1e7e34;
        }

        /* Botón para agregar nuevo cliente */
        .agregar-btn {
            padding: 10px 20px;
            background-color: #17a2b8;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 16px;
            margin-bottom: 20px;
            cursor: pointer;
            display: inline-block;
        }

        .agregar-btn:hover {
            background-color: #117a8b;
        }

        /* Formulario para agregar nuevo cliente */
        #agregar-modal .modal-content {
            width: 90%;
            max-width: 500px;
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

    <!-- Sección Administrar Clientes -->
    <main>
        <section class="admin-clientes">
            <h2>Administrar Clientes</h2>
            <button id="abrirAgregarModal" class="agregar-btn">Agregar Nuevo Cliente</button>
            <table class="clientes-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>RFC</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cliente['id']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['rfc']); ?></td>
                            <td>
                                <button class="btn btn-editar" data-id="<?php echo $cliente['id']; ?>">Editar</button>
                                <button class="btn btn-eliminar" data-id="<?php echo $cliente['id']; ?>">Eliminar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Modal para Editar Cliente -->
    <div id="editarModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Editar Cliente</h3>
            <form id="editar-form" method="POST" action="editar_cliente.php">
                <input type="hidden" name="id" id="editar-id">
                <div>
                    <label for="editar-nombre">Nombre:</label>
                    <input type="text" id="editar-nombre" name="nombre" required>
                </div>
                <div>
                    <label for="editar-email">Email:</label>
                    <input type="email" id="editar-email" name="email" required>
                </div>
                <div>
                    <label for="editar-rfc">RFC:</label>
                    <input type="text" id="editar-rfc" name="rfc" maxlength="13" required>
                </div>
                <button type="submit">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal para Agregar Nuevo Cliente -->
    <div id="agregarModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Agregar Nuevo Cliente</h3>
            <form id="agregar-form" method="POST" action="agregar_cliente.php">
                <div>
                    <label for="agregar-nombre">Nombre:</label>
                    <input type="text" id="agregar-nombre" name="nombre" required>
                </div>
                <div>
                    <label for="agregar-email">Email:</label>
                    <input type="email" id="agregar-email" name="email" required>
                </div>
                <div>
                    <label for="agregar-password">Contraseña:</label>
                    <input type="password" id="agregar-password" name="password" required>
                </div>
                <div>
                    <label for="agregar-rfc">RFC:</label>
                    <input type="text" id="agregar-rfc" name="rfc" maxlength="13" required>
                </div>
                <button type="submit">Agregar Cliente</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Modern Home. Esta página es ficticia con fines escolares, no lucrativa.</p>
    </footer>

    <!-- Scripts para la interactividad -->
    <script>
        // Funcionalidad para abrir y cerrar modales
        const modales = document.querySelectorAll('.modal');
        const abrirAgregarModal = document.getElementById('abrirAgregarModal');
        const cerrarModales = document.querySelectorAll('.close-modal');

        abrirAgregarModal.addEventListener('click', () => {
            document.getElementById('agregarModal').style.display = 'block';
        });

        cerrarModales.forEach(cerrar => {
            cerrar.addEventListener('click', () => {
                modales.forEach(modal => {
                    modal.style.display = 'none';
                });
            });
        });

        // Cerrar el modal al hacer clic fuera de él
        window.addEventListener('click', (event) => {
            modales.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // Funcionalidad para abrir el modal de editar cliente con los datos correspondientes
        const botonesEditar = document.querySelectorAll('.btn-editar');
        botonesEditar.forEach(boton => {
            boton.addEventListener('click', () => {
                const clienteId = boton.getAttribute('data-id');

                // Realizar una solicitud AJAX para obtener los datos del cliente
                fetch(`obtener_cliente.php?id=${clienteId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('editar-id').value = data.cliente.id;
                            document.getElementById('editar-nombre').value = data.cliente.nombre;
                            document.getElementById('editar-email').value = data.cliente.email;
                            document.getElementById('editar-rfc').value = data.cliente.rfc;
                            document.getElementById('editarModal').style.display = 'block';
                        } else {
                            alert('Error al obtener los datos del cliente.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });

        // Funcionalidad para eliminar cliente
        const botonesEliminar = document.querySelectorAll('.btn-eliminar');
        botonesEliminar.forEach(boton => {
            boton.addEventListener('click', () => {
                const clienteId = boton.getAttribute('data-id');
                if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
                    // Redirigir a eliminar_cliente.php con el ID del cliente
                    window.location.href = `eliminar_cliente.php?id=${clienteId}`;
                }
            });
        });
    </script>

</body>
</html>
