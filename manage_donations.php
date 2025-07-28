<?php
// manage_donations.php

include_once 'db_connection.php';
include_once 'Project.php';
include_once 'Donor.php';
include_once 'Donation.php';

$database = new Database();
$db = $database->getConnection();

$project = new Project($db);
$donor = new Donor($db);
$donation = new Donation($db);

$message = '';

// Lógica para manejar el envío del formulario de donación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_donation') {
        $donation->id_proyecto = $_POST['id_proyecto'];
        $donation->id_donante = $_POST['id_donante'];
        $donation->monto = $_POST['monto'];
        $donation->fecha = $_POST['fecha'];

        if ($donation->create()) {
            $message = "<div class='alert success'>Donación registrada correctamente.</div>";
        } else {
            $message = "<div class='alert error'>No se pudo registrar la donación. Asegúrate de que los IDs existan y los datos sean válidos.</div>";
        }
    }
}

// Obtener proyectos y donantes para los select boxes
$projects_stmt = $project->read();
$donors_stmt = $donor->read();

// Obtener todas las donaciones para mostrar la tabla
$donations_stmt = $donation->read();
$num_donations = $donations_stmt->rowCount();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Donaciones</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@700&display=swap" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: bold;
        }
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
    </style>
    <script>
        function validateDonationForm() {
            var proyecto = document.getElementById('id_proyecto').value;
            var donante = document.getElementById('id_donante').value;
            var monto = document.getElementById('monto_donacion').value;
            var fecha = document.getElementById('fecha_donacion').value;

            if (proyecto === '' || donante === '' || monto.trim() === '' || fecha.trim() === '') {
                alert('Todos los campos de la donación son obligatorios.');
                return false;
            }
            if (isNaN(monto) || parseFloat(monto) <= 0) {
                alert('Por favor, ingresa un monto de donación válido (número positivo).');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <header>
        <h1>Administración de Donaciones</h1>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="manage_projects.php">Administrar Proyectos</a></li>
                <li><a href="manage_donors.php">Administrar Donantes</a></li>
                <li><a href="reports.php">Ver Informes</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <?php echo $message; // Muestra mensajes de éxito/error aquí ?>

        <h2>Disponibilidad de Proyectos para Donaciones</h2>
        <?php
        try {
            // Se considera que un proyecto está disponible si su estado es 'activo' y su fecha de fin no ha pasado.
            $query_available_projects = "
                SELECT
                    id_proyecto,
                    nombre,
                    descripcion,
                    fecha_fin,
                    estado
                FROM
                    PROYECTO
                WHERE
                    estado = 'activo' AND fecha_fin >= CURDATE()
                ORDER BY
                    fecha_fin ASC;
            ";
            $stmt_available_projects = $db->prepare($query_available_projects);
            $stmt_available_projects->execute();

            if ($stmt_available_projects->rowCount() > 0) {
                echo "<table>";
                echo "<thead><tr><th>ID</th><th>Nombre del Proyecto</th><th>Descripción</th><th>Fecha de Fin</th><th>Estado</th></tr></thead>";
                echo "<tbody>";
                while ($row = $stmt_available_projects->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . $row['id_proyecto'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
                    echo "<td>" . date('d-m-Y', strtotime($row['fecha_fin'])) . "</td>";
                    echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>No hay proyectos activos disponibles para recibir donaciones en este momento.</p>";
            }
        } catch (PDOException $e) {
            echo "<div class='alert error'>Error al obtener proyectos disponibles: " . $e->getMessage() . "</div>";
        }
        ?>

        <h2>Registrar Nueva Donación</h2>
        <form action="manage_donations.php" method="POST" onsubmit="return validateDonationForm();">
            <input type="hidden" name="action" value="create_donation">
            <div class="form-group">
                <label for="id_proyecto">Proyecto:</label>
                <select id="id_proyecto" name="id_proyecto" required>
                    <option value="">Seleccione un proyecto</option>
                    <?php
                    // Volver a ejecutar para el select box
                    $projects_stmt->execute();
                    if ($projects_stmt->rowCount() > 0) {
                        while ($row = $projects_stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $row['id_proyecto'] . "'>" . htmlspecialchars($row['nombre']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_donante">Donante:</label>
                <select id="id_donante" name="id_donante" required>
                    <option value="">Seleccione un donante</option>
                    <?php
                    // Volver a ejecutar para el select box
                    $donors_stmt->execute();
                    if ($donors_stmt->rowCount() > 0) {
                        while ($row = $donors_stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $row['id_donante'] . "'>" . htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="monto_donacion">Monto:</label>
                <input type="number" id="monto_donacion" name="monto" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="fecha">Fecha de Donación:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <button type="submit">Registrar Donación</button>
            </div>
        </form>

        <h2 style="margin-top: 40px;">Contenido de la Tabla DONACION</h2>
        <?php if ($num_donations > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Donación</th>
                        <th>Proyecto</th>
                        <th>Donante</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Volver a ejecutar para mostrar la tabla después de una posible inserción
                    $donations_stmt = $donation->read();
                    while ($row = $donations_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo $row['id_donacion']; ?></td>
                            <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['donor_name'] . ' ' . $row['donor_lastname']); ?></td>
                            <td>$<?php echo number_format($row['monto'], 2, ',', '.'); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['fecha'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay donaciones registradas aún.</p>
        <?php endif; ?>

        <h2 style="margin-top: 40px;">Proyectos con Más de Dos Donaciones Registradas</h2>
        <?php
        try {
            $query_projects_multiple_donations = "
                SELECT
                    P.nombre AS NombreProyecto,
                    COUNT(DA.id_donacion) AS NumeroDeDonaciones,
                    SUM(DA.monto) AS MontoTotalRecaudadoPorDonaciones
                FROM
                    PROYECTO P
                JOIN
                    DONACION DA ON P.id_proyecto = DA.id_proyecto
                GROUP BY
                    P.id_proyecto, P.nombre
                HAVING
                    COUNT(DA.id_donacion) > 2
                ORDER BY
                    NumeroDeDonaciones DESC, MontoTotalRecaudadoPorDonaciones DESC;
            ";
            $stmt_projects_multiple_donations = $db->prepare($query_projects_multiple_donations);
            $stmt_projects_multiple_donations->execute();

            if ($stmt_projects_multiple_donations->rowCount() > 0) {
                echo "<table>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>Proyecto</th>";
                echo "<th>Número de Donaciones</th>";
                echo "<th>Monto Total Recaudado</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                while ($row = $stmt_projects_multiple_donations->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['NombreProyecto']) . "</td>";
                    echo "<td>" . $row['NumeroDeDonaciones'] . "</td>";
                    echo "<td>$" . number_format($row['MontoTotalRecaudadoPorDonaciones'], 2, ',', '.') . "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>No hay proyectos con más de dos donaciones registradas.</p>";
            }
        } catch (PDOException $e) {
            echo "<div class='alert error'>Error al generar el informe de proyectos con múltiples donaciones: " . $e->getMessage() . "</div>";
        }
        ?>
    </main>

</body>
</html>