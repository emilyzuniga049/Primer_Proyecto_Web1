<?php
require_once __DIR__ . '/../Actions/session_driver.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$driver_id = (int)($_SESSION['user_id'] ?? 0);

// Traer vehículos del driver
$vehicles = [];
$sql = "SELECT id, plate, color, make, model, year, seat_capacity, photo_path, created_at
        FROM vehicles WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $driver_id);
$stmt->execute();
$vehicles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Aventones · Vehicles</title>
  <link rel="stylesheet" href="../../css/estilos.css">
  <link rel="stylesheet" href="../../css/estilo_nav_logo.css">
  <link rel="stylesheet" href="../../css/driver_vehicles.css?v=4">
</head>
<body>
<header class="main-header">
  <div class="logo-container">
    <img src="../../Img/icono_carros.png" class="logo" alt="Logo"><h1>AVENTONES</h1>
  </div>
  <nav class="nav-bar">
    <a href="my_rides.php">My Rides</a>
    <a href="new_ride.php">New Ride</a>
    <a href="vehicles.php" class="active">Vehicles</a>
    <a href="bookings.php">Bookings</a>
    <div class="user-menu" style="margin-left:auto">
      <img src="../../Img/user_icon.png" class="user-icon" alt="User">
      <div class="user-dropdown">
        <a href="../Actions/logout.php">Logout</a>
        <a href="../Profile/edit_profile.php">Profile</a>
        <a href="../Profile/configuration.php">Settings</a>
      </div>
    </div>
  </nav>
</header>

<main class="wrap">
  <?php if(isset($_GET['error'])): ?>
    <div class="msg err"><?= e($_GET['error']) ?></div>
  <?php endif; ?>
  <?php if(isset($_GET['msg'])): ?>
    <div class="msg ok"><?= e($_GET['msg']) ?></div>
  <?php endif; ?>

  <div class="row">>
    <div class="card">
      <div class="section-head"><h2>Add Vehicle</h2></div>
      <form action="../DAO/vehicle_create.php" method="post" enctype="multipart/form-data" class="grid2" autocomplete="off">
        <input type="hidden" name="user_id" value="<?= (int)$driver_id ?>">
        <div>
          <label>Plate</label>
          <input type="text" name="plate" required placeholder="ABC-123">
        </div>
        <div>
          <label>Color</label>
          <input type="text" name="color" placeholder="Blue">
        </div>
        <div>
          <label>Make</label>
          <input type="text" name="make" required placeholder="Toyota">
        </div>
        <div>
          <label>Model</label>
          <input type="text" name="model" required placeholder="Corolla">
        </div>
        <div>
          <label>Year</label>
          <input type="number" name="year" min="1950" max="2100" required placeholder="2018">
        </div>
        <div>
          <label>Seats</label>
          <input type="number" name="seat_capacity" min="1" max="9" required placeholder="4">
        </div>
        <div style="grid-column:1 / -1">
          <label>Photo (optional)</label>
          <div class="file-upload">
            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
            <label for="photo" class="file-label">Choose Photo</label>
            <span id="file-name" class="file-name">No file selected</span>
          </div>
        </div>
        <div style="grid-column:1 / -1;text-align:right">
          <button class="btn primary" type="submit">Save Vehicle</button>
        </div>
      </form>
    </div>

    <div class="card">
      <div class="section-head"><h2>My Vehicles</h2></div>

      <?php if(empty($vehicles)): ?>
        <p class="muted">No vehicles yet.</p>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>Photo</th><th>Plate</th><th>Make/Model</th><th>Year</th>
              <th>Color</th><th>Seats</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($vehicles as $v): 
            $photo = $v['photo_path'] ? '../../'.ltrim($v['photo_path'],'/') : '../../Img/car_placeholder.png';
          ?>
            <tr>
              <td style="width:70px">
                <img src="<?= e($photo) ?>" alt="car" class="car-thumb">
              </td>
              <td><?= e($v['plate']) ?></td>
              <td><?= e($v['make'].' '.$v['model']) ?></td>
              <td><?= (int)$v['year'] ?></td>
              <td><?= e($v['color'] ?: '—') ?></td>
              <td><?= (int)$v['seat_capacity'] ?></td>
              <td class="actions">
                <a class="btn sm" href="vehicle_edit.php?id=<?= (int)$v['id'] ?>">Edit</a>
                <form action="../DAO/vehicle_delete.php" method="post" onsubmit="return confirm('Delete this vehicle?')" style="display:inline">
                  <input type="hidden" name="vehicle_id" value="<?= (int)$v['id'] ?>">
                  <button class="btn danger sm" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</main>
</body>
</html>
