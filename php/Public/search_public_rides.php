<?php
//require_once __DIR__ . '/../Actions/session_passenger.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Rides - Aventones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Estilos: desde php/Passenger/ hasta /css es ../../ -->
    <link rel="stylesheet" href="../../css/estilos.css">
    <link rel="stylesheet" href="../../css/estilo_nav_logo.css">
    <link rel="stylesheet" href="../../css/estilos_searchrides.css?v=1">

    <!-- JS: desde php/Passenger/ hasta /js es ../../ -->
    <script src="../../js/search_public_rides.js" defer></script>
</head>
<body>

<header class="main-header">
    <div class="logo-container">
        <img src="../../Img/icono_carros.png" alt="Logo" class="logo">
        <h1>AVENTONES</h1>
    </div>
    <nav class="nav-bar">
        <a href="#" class="active">Home</a>

        <div class="search-container">
            <div class="user-menu">
                <img src="../../Img/user_icon.png" alt="User" class="user-icon">
                <div class="user-dropdown">
                    <a href="../../Index.html">Login</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<div class="rides-container">
    <h2>Search rides</h2>

    <div class="search-box">
        <div class="search-row">
            <div class="filters-left">
                <div class="inline-group">
                    <label for="from">From</label>
                    <select id="from">
                        <option value="">- Select origin -</option>
                    </select>
                </div>

                <div class="inline-group">
                    <label for="to">To</label>
                    <select id="to">
                        <option value="">- Select destination -</option>
                    </select>
                </div>
            </div>

            <button class="find-btn">Find rides</button>
        </div>

        <div class="days-line">
            <label>Days</label>
            <div class="days-checkboxes">
                <label><input type="checkbox" checked>Mon</label>
                <label><input type="checkbox" checked>Tue</label>
                <label><input type="checkbox" checked>Wed</label>
                <label><input type="checkbox" checked>Thu</label>
                <label><input type="checkbox" checked>Fri</label>
                <label><input type="checkbox" checked>Sat</label>
                <label><input type="checkbox" checked>Sun</label>
            </div>
        </div>
    </div>

    <div class="sort-line">
        <label>Sort by</label>
        <div class="sort-controls">
            <select id="sort-by">
                <option value="date">Date</option>
                <option value="origin">Origin</option>
                <option value="destination">Destination</option>
            </select>
            <select id="sort-dir">
                <option value="asc">Asc</option>
                <option value="desc">Desc</option>
            </select>
            <button class="sort-apply">Apply</button>
        </div>
    </div>

    <p class="search-result" style="display:none;">Rides found from <b>Any</b> to <b>Any</b></p>

    <div class="table-wrap">
        <table class="rides-table">
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>From</th>
                    <th>To</th>
                    <th>When</th>
                    <th>Seats</th>
                    <th>Car</th>
                    <th>Fee</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
               
                <tr>
                    <td colspan="7" style="text-align:center; opacity:.8;"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="map-container">
        <iframe
            class="map-iframe"
            src="https://www.google.com/maps?q=Costa%20Rica&z=7&output=embed"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen>
        </iframe>
    </div>
</div>

<footer class="footer">
    <div class="footer-links">
        <a href="">Home</a> |
        <a href="">Rides</a> |
        <a href="">Bookings</a> |
        <a href="">Settings</a> |
        <a href="../../Index.html">Login</a> |
        <a href="../../Register_User/Index.html">Register</a>
    </div>
    <p class="footer-copy">© Aventones.com</p>
</footer>

</body>
</html>
