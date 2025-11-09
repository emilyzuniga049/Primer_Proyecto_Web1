<?php
require_once __DIR__ . '/../Actions/session_driver.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$driver_id  = (int)($_SESSION['user_id'] ?? 0);
$vehicle_id = (int)($_GET['id'] ?? 0);

$sql = "SELECT id, user_id, plate, color, make, model, year, seat_capacity, photo_path
        FROM vehicles WHERE id=? AND user_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $vehicle_id, $driver_id);
$stmt->execute();
$v = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$v) { header('Location: vehicles.php?error=Vehicle+not+found'); exit; }

$photo = $v['photo_path'] ? '../../'.ltrim($v['photo_path'],'/') : '../../Img/car_placeholder.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Vehicle</title>
  <link rel="stylesheet" href="../../css/estilos.css">
  <link rel="stylesheet" href="../../css/estilo_nav_logo.css">
  <link rel="stylesheet" href="../../css/driver_vehicles.css?v=1">
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
      </div>
    </div>
  </nav>
</header>

<main class="wrap">
  <?php if(isset($_GET['error'])): ?><div class="msg err"><?= e($_GET['error']) ?></div><?php endif; ?>
  <?php if(isset($_GET['msg'])): ?><div class="msg ok"><?= e($_GET['msg']) ?></div><?php endif; ?>

  <div class="card" style="max-width:760px;margin:0 auto;">
    <div class="section-head">
      <h2>Edit Vehicle · <?= e($v['plate']) ?></h2>
      <img class="car-thumb" src="<?= e($photo) ?>" alt="car" style="border-radius:8px;">
    </div>

    <form action="../DAO/vehicle_update.php" method="post" enctype="multipart/form-data" class="grid2">
      <input type="hidden" name="vehicle_id" value="<?= (int)$v['id'] ?>">
      <div>
        <label>Plate</label>
        <input type="text" name="plate" required value="<?= e($v['plate']) ?>">
      </div>
      <div>
        <label>Color</label>
        <input type="text" name="color" value="<?= e($v['color']) ?>">
      </div>
      <div>
        <label>Make</label>
        <input type="text" name="make" required value="<?= e($v['make']) ?>">
      </div>
      <div>
        <label>Model</label>
        <input type="text" name="model" required value="<?= e($v['model']) ?>">
      </div>
      <div>
        <label>Year</label>
        <input type="number" name="year" min="1950" max="2100" required value="<?= (int)$v['year'] ?>">
      </div>
      <div>
        <label>Seats</label>
        <input type="number" name="seat_capacity" min="1" max="9" required value="<?= (int)$v['seat_capacity'] ?>">
      </div>
      <div style="grid-column:1 / -1">
        <label>Replace Photo (optional)</label>
        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
      </div>
      <div style="grid-column:1 / -1;text-align:right">
        <a class="btn" href="vehicles.php">Cancel</a>
        <button class="btn primary" type="submit">Save Changes</button>
      </div>
    </form>
  </div>
</main>
</body>
</html>
