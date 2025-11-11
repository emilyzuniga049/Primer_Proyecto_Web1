<?php
require_once __DIR__ . '/../Actions/session_driver.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$driver_id = (int)($_SESSION['user_id'] ?? 0);
$ride_id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ride_id <= 0) {
  header('Location: my_rides.php?error=Invalid ride');
  exit;
}


$sql = "SELECT id, driver_id, vehicle_id, name, origin, destination,
               departure_time, days_set, seat_price, total_seats, available_seats
        FROM rides
        WHERE id = ? AND driver_id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $ride_id, $driver_id);
$stmt->execute();
$res = $stmt->get_result();
$ride = $res->fetch_assoc();
$stmt->close();

if (!$ride) {
  header('Location: my_rides.php?error=Ride not found');
  exit;
}


$vehicles = [];
$sqlV = "SELECT id, make, model, year FROM vehicles WHERE user_id = ? ORDER BY year DESC, make, model";
$stmtV = $conn->prepare($sqlV);
$stmtV->bind_param('i', $driver_id);
$stmtV->execute();
$vehicles = $stmtV->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtV->close();

// Días seleccionados
$selected_days = $ride['days_set'] ? explode(',', strtolower($ride['days_set'])) : [];
$day_labels = ['mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat','sun'=>'Sun'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Aventones · Edit Ride</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- estilos -->
  <link rel="stylesheet" href="../../css/estilos.css">
  <link rel="stylesheet" href="../../css/estilo_nav_logo.css">
  <link rel="stylesheet" href="../../css/estilos_NewEditRide.css?v=3">
</head>
<body>
  <header class="main-header">
    <div class="logo-container">
      <img src="../../Img/icono_carros.png" class="logo" alt="Logo">
      <h1>AVENTONES</h1>
    </div>
    <nav class="nav-bar">
      <a href="my_rides.php" class="active">My Rides</a>
      <a href="new_ride.php">New Ride</a>
      <a href="vehicles.php">Vehicles</a>
      <a href="bookings.php">Bookings</a>
      <div class="user-menu" style="margin-left:auto">
        <img src="../../Img/user_icon.png" class="user-icon" alt="User Icon">
        <div class="user-dropdown">
          <a href="../../php/Actions/logout.php">Logout</a>
          <a href="../Edit_Profile/index.php">Profile</a>
          <a href="../Profile/configuration.php">Settings</a>
        </div>
      </div>
    </nav>
  </header>

  <main class="form-container">
    <h2>Edit Ride</h2>

    <?php if(isset($_GET['error'])): ?>
      <div class="msg err"><?= e($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['msg'])): ?>
      <div class="msg ok"><?= e($_GET['msg']) ?></div>
    <?php endif; ?>

    <form action="../DAO/edit_ride_db.php" method="post" class="grid2" autocomplete="off">
      <input type="hidden" name="ride_id" value="<?= (int)$ride['id'] ?>">

      <div>
        <label for="name">Ride Name (optional)</label>
        <input type="text" id="name" name="name" value="<?= e($ride['name'] ?? '') ?>">
      </div>

      <div>
        <label for="vehicle_id">Vehicle</label>
        <select name="vehicle_id" id="vehicle_id" required>
          <option value="">Select vehicle</option>
          <?php foreach($vehicles as $v): ?>
            <option value="<?= (int)$v['id'] ?>" <?= ((int)$v['id'] === (int)$ride['vehicle_id']) ? 'selected' : '' ?>>
              <?= e($v['make'].' '.$v['model'].' '.$v['year']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="origin">Origin</label>
        <input type="text" id="origin" name="origin" required value="<?= e($ride['origin']) ?>">
      </div>

      <div>
        <label for="destination">Destination</label>
        <input type="text" id="destination" name="destination" required value="<?= e($ride['destination']) ?>">
      </div>

      <div style="grid-column:1 / -1">
        <label>Days</label>
        <div class="weekdays" style="display:flex; gap:10px; flex-wrap:wrap">
          <?php foreach($day_labels as $val=>$label): ?>
            <label style="display:inline-flex; gap:6px; align-items:center; background:#0b1220; border:1px solid #1f2937; padding:8px 10px; border-radius:8px;">
              <input type="checkbox" name="days[]" value="<?= $val ?>" <?= in_array($val,$selected_days,true) ? 'checked' : '' ?>> <?= $label ?>
            </label>
          <?php endforeach; ?>
        </div>
        <small style="color:#9ca3af">Select one or more days.</small>
      </div>

      <div>
        <label for="departure_time">Time</label>
        <input type="time" id="departure_time" name="departure_time" required
               value="<?= e(substr($ride['departure_time'],0,5)) ?>">
      </div>

      <div>
        <label for="seat_price">Seat Price (₡)</label>
        <input type="number" id="seat_price" name="seat_price" step="0.01" min="0"
               value="<?= e((string)$ride['seat_price']) ?>" required>
      </div>

      <div>
        <label for="total_seats">Total Seats</label>
        <input type="number" id="total_seats" name="total_seats" min="1"
               value="<?= (int)$ride['total_seats'] ?>" required>
      </div>

      <div style="grid-column:1 / -1; text-align:right">
        <a class="btn" href="my_rides.php">Cancel</a>
        <button type="submit" class="btn primary">Save Changes</button>
      </div>
    </form>
  </main>
</body>
</html>
