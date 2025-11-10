<?php
    require_once __DIR__ . '/../Actions/session_passenger.php'; 
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Ride Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Estilos generales y específicos -->
    <link rel="stylesheet" href="../../css/estilos.css" />
    <link rel="stylesheet" href="../../css/estilo_nav_logo.css" />
    <link rel="stylesheet" href="../../css/layout_base.css" />
    <link rel="stylesheet" href="../../css/estilos_Ride_Details.css" />
    <script src="../../js/ride_details.js" defer></script>
    <script src="../../js/ride_request.js"></script>
  </head>
  <body>
    <header class="main-header">
      <div class="logo-container">
        <img src="../../Img/icono_carros.png" alt="Logo" class="logo">
        <h1>AVENTONES</h1>
      </div>

      <nav class="nav-bar"> <!-- Barra de navegación -->
        <a href="search_rides.php" class="active">Home</a>
        <a href="bookings_passenger.php">Bookings</a>

        <div class="search-container"> <!-- Barra de búsqueda y menú de usuario -->
          <input type="text" placeholder="Search...">
          <div class="user-menu">
            <img src="../../Img/user_icon.png" alt="User" class="user-icon">
            <div class="user-dropdown">
              <a href="../Actions/logout.php" id="Logout-btn">Logout</a>
              <a href="../Profile/edit_profile.php">Profile</a>
              <a href="../Profile/configuration.php">Settings</a>
            </div>
          </div>
        </div>

      </nav>
    </header>

    <main class="ride-details-container">
      <h2 class="ride-title">Ride Details</h2>
      <div class="ride-profile">
        <img src="../../Img/user_icon.png" alt="Usuario" class="profile-img">
        <p class="username">barroyo</p>
      </div>
      <form class="ride-form">
        <div class="route-info">  <!-- Ruta (origen y destino) -->
          <label>Departure from <span>Quesada</span></label>
          <label>Arrive To <span>Zarcero</span></label>
        </div>
        <div class="days-selection">   <!-- Días de la semana seleccionados -->
          <label class="days-label">Days</label>
          <div class="days-checkboxes">
            <label><input type="checkbox" checked> Mon</label>
            <label><input type="checkbox" checked> Tue</label>
            <label><input type="checkbox" checked> Wed</label>
            <label><input type="checkbox" checked> Thu</label>
            <label><input type="checkbox" checked> Fri</label>
            <label><input type="checkbox" checked> Sat</label>
            <label><input type="checkbox" checked> Sun</label>
          </div>
        </div>
        <div class="ride-fields">  <!-- Detalles del viaje -->
          <div class="ride-field-inline">
            <label>Time</label>
            <input type="time" value="10:00" />
          </div>
          <div class="ride-field-inline">
            <label>Seats</label>
            <input type="number" value="2" min="1" />
          </div>
          <div class="ride-field-inline">
            <label>Fee</label>
            <input type="number" value="15" min="0" />
          </div>
        </div>
        <fieldset class="vehicle-details"> <!-- Información del vehículo -->
          <legend>Vehicle Details</legend>
          <div class="vehicle-field">
            <label>Make</label>
            <input type="text" id="make" readonly>
          </div>
          <div class="vehicle-field">
            <label>Model</label>
            <input type="text" id="model" readonly>
          </div>
          <div class="vehicle-field">
            <label>Year</label>
            <input type="text" id="year" readonly>
          </div>
        </fieldset>
        <div class="form-actions">
          <a href="search_rides.php" class="cancel-link">Cancel</a>
          <button type="button" id="request-btn" class="request-btn">Request</button>
        </div>
      </form>
    </main>

    <footer class="footer">
      <div class="footer-links">
        <a href="search_rides.php">Home</a> |
        <a href="bookings_passenger.php">Bookings</a> |
        <a href="../Profile/configuration.php">Settings</a> |
        <a href="../Actions/logout.php">Login</a> |
        <a href="../Actions/logout.php">Register</a>
      </div>
      <p class="footer-copy">© Aventones.com</p>
    </footer>
  </body>
</html>

