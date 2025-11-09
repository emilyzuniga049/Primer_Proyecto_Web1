<?php
require_once __DIR__ . '/../Actions/session_driver.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

$driver_id = (int)($_SESSION['user_id'] ?? 0);

$vehicles = [];
if ($driver_id) {
  $sql = "SELECT id, make, model, year FROM vehicles WHERE user_id = ? ORDER BY year DESC, make, model";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $driver_id);
  $stmt->execute();
  $vehicles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Aventones · New Ride</title>
  <link rel="stylesheet" href="../../css/estilos.css">
  <link rel="stylesheet" href="../../css/estilo_nav_logo.css">
  <link rel="stylesheet" href="../../css/estilos_NewEditRide.css?v=2">
</head>
<body>
  <header class="main-header">
    <div class="logo-container">
      <img src="../../Img/icono_carros.png" class="logo" alt="Logo">
      <h1>AVENTONES</h1>
    </div>
    <nav class="nav-bar">
      <a href="my_rides.php">My Rides</a>
      <a href="new_ride.php" class="active">New Ride</a>
      <a href="./vehicles.php">Vehicles</a>
      <a href="bookings.php">Bookings</a>
      <div class="search-container">
        <div class="user-menu" style="margin-left:auto">
          <img src="../../Img/user_icon.png" class="user-icon" alt="User Icon">
          <div class="user-dropdown">
            <a href="../Actions/logout.php">Logout</a>
            <a href="../Edit_Profile/index.php">Profile</a>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <main class="form-container">
    <h2>Create a New Ride</h2>

    <form action="../DAO/create_new_ride.php" method="post" class="grid2" autocomplete="off">
      <div>
        <label for="name">Ride Name (optional)</label>
        <input type="text" id="name" name="name" placeholder="Morning commute">
      </div>

      <div>
        <label for="vehicle_id">Vehicle</label>
        <select name="vehicle_id" id="vehicle_id" required>
          <option value="">Select vehicle</option>
          <?php foreach($vehicles as $v): ?>
            <option value="<?= (int)$v['id'] ?>">
              <?= htmlspecialchars($v['make'].' '.$v['model'].' '.$v['year'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="origin">Origin</label>
        <input type="text" id="origin" name="origin" required>
      </div>

      <div>
        <label for="destination">Destination</label>
        <input type="text" id="destination" name="destination" required>
      </div>

      <div style="grid-column:1 / -1">
        <label>Days</label>
        <div class="weekdays" style="display:flex; gap:10px; flex-wrap:wrap">
          <?php
          $days = ['mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat','sun'=>'Sun'];
          foreach($days as $val=>$label): ?>
            <label style="display:inline-flex; gap:6px; align-items:center; background:#0b1220; border:1px solid #1f2937; padding:8px 10px; border-radius:8px;">
              <input type="checkbox" name="days[]" value="<?= $val ?>"> <?= $label ?>
            </label>
          <?php endforeach; ?>
        </div>
        <small style="color:#9ca3af">Select one or more days.</small>
      </div>

      <div>
        <label for="departure_time">Time</label>
        <input type="time" id="departure_time" name="departure_time" required>
      </div>

      <div>
        <label for="seat_price">Seat Price (₡)</label>
        <input type="number" id="seat_price" name="seat_price" step="0.01" min="0" required>
      </div>

      <div>
        <label for="total_seats">Total Seats</label>
        <input type="number" id="total_seats" name="total_seats" min="1" required>
      </div>

      <div style="grid-column:1 / -1;text-align:right">
        <button type="submit" class="btn primary">Create Ride</button>
        <a class="btn" href="my_rides.php">Cancel</a>
      </div>
    </form>
  </main>
</body>
</html>

