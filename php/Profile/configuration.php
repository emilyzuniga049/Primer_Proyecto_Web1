<?php
require_once __DIR__ . '/../Actions/session.php'; 
require_once __DIR__ . '/../Conexion/db_conexion.php';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
  header('Location: /Index.html?error=Please login');
  exit;
}

// Solo seleccionamos lo que necesitamos
$sql = "SELECT first_name, bio FROM users WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
  header('Location: /Index.html?error=User not found');
  exit;
}

$firstName = $user['first_name'] ?? '';
$bio = $user['bio'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Configuration - Aventones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- estilos -->
    <link rel="stylesheet" href="../../css/estilos.css">
    <link rel="stylesheet" href="../../css/estilo_nav_logo.css">
    <link rel="stylesheet" href="../../css/estilos_configuration.css">
    <script src="../../js/configuration.js" defer></script>
</head>
<body>

    <!-- Encabezado principal con logo y barra de navegación -->
    <header class="main-header">
        <div class="logo-container">
            <img src="../../Img/icono_carros.png" alt="Logo" class="logo">
            <h1>AVENTONES</h1>
        </div>
        <nav class="nav-bar">
            <?php if(($_SESSION['user_role'] ?? '') === 'driver'): ?>
                <a href="../Driver/my_rides.php">My Rides</a>
                <a href="../Driver/new_ride.php">New Ride</a>
                <a href="../Driver/vehicles.php">Vehicles</a>
                <a href="../Driver/bookings.php">Bookings</a>
            <?php else: ?>
                <a href="../Passenger/search_rides.php">Search Rides</a>
                <a href="../Passenger/bookings_passenger.php">My Bookings</a>
            <?php endif; ?>
                <div class="user-menu" style="margin-left:auto">
                    <img src="../../Img/user_icon.png" class="user-icon" alt="User Icon">
                    <div class="user-dropdown">
                        <a href="../Actions/logout.php">Logout</a>
                        <a class="active" href="./configuration.php">Profile</a>
                    </div>
                </div>
        </nav>
    </header>

    <!-- Contenedor principal de configuración -->
    <div class="configuration-container">
        <h2>Configuration</h2>
        <form class="configuration-form" method="POST" action="../DAO/save_configuration.php">
            <!-- Campo para el nombre público -->
            <label for="public-name">Public Name</label>
            <input type="text" id="public-name" name="public-name" value="<?= e($firstName) ?>" readonly>

            <!-- Campo para la biografía -->
            <label for="public-bio">Public Bio</label>
            <textarea id="public-bio" name="public-bio" rows="6"><?= e($bio) ?></textarea>

            <!-- Acciones para cancelar o guardar -->
            <div class="button-group">
                <a href="javascript:history.back()" class="cancel-btn">Cancel</a>
                <button type="submit" class="save-btn">Save</button>
            </div>
        </form>
    </div>

</body>
</html>

