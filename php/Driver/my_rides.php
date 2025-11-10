<?php
require_once __DIR__ . '/../Actions/session_driver.php'; 
require_once __DIR__ . '/../Conexion/db_conexion.php';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$driverId = (int)($_SESSION['user_id'] ?? 0);

$sql = "
  SELECT r.id, r.name, r.origin, r.destination,
         r.departure_time, r.days_set,
         r.seat_price, r.total_seats, r.available_seats,
         v.make, v.model, v.year
  FROM rides r
  LEFT JOIN vehicles v
         ON v.id = r.vehicle_id AND v.user_id = r.driver_id
  WHERE r.driver_id = ?
  ORDER BY r.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $driverId);
$stmt->execute();
$rides = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Aventones · My Rides</title>

  <!-- estilos -->
  <link rel="stylesheet" href="../../css/estilos.css?v=4">
  <link rel="stylesheet" href="../../css/estilo_nav_logo.css?v=4">
  <link rel="stylesheet" href="../../css/estilo_MyRide.css?v=4">
  <link rel="stylesheet" href="../../css/estilo_admin_users.css?v=4"> 

</head>
<body>
  <header class="main-header">
    <div class="logo-container">
      <img src="../../Img/icono_carros.png" class="logo" alt="Logo">
      <h1>AVENTONES</h1>
    </div>
    <nav class="nav-bar">
      <a class="active" href="./my_rides.php">Rides</a>
      <a href="./new_ride.php">New Ride</a>
      <a href="./vehicles.php">Vehicles</a>
      <a href="./bookings.php">Bookings</a>
      <div class="user-menu">
        <img src="../../Img/user_icon.png" class="user-icon" alt="User Icon">
        <div class="user-dropdown">
          <a href="../Profile/edit_profile.php">Profile</a>
          <a href="../Actions/logout.php">Logout</a>
        </div>
      </div>
    </nav>
  </header>

  <main class="wrap">
    <h2>My Rides</h2>

    <div class="card" style="margin-bottom:14px;">
      <a class="btn primary" href="./new_ride.php">+ New Ride</a>
    </div>

    <div class="card">
      <?php if (count($rides) === 0): ?>
        <p class="msg">You don't have rides yet. Create your first one.</p>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>From</th>
              <th>To</th>
              <th>Date & Time</th>
              <th>Seats</th>
              <th>Vehicle</th>
              <th>Fee</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rides as $r): ?>
            <tr>
              <td><?= e($r['name'] ?: '-') ?></td>
              <td><?= e($r['origin']) ?></td>
              <td><?= e($r['destination']) ?></td>
              <td>
                <?php
                    $map = ['mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat','sun'=>'Sun'];
                    $days = $r['days_set'] ? explode(',', $r['days_set']) : [];
                    $labels = array_map(fn($d)=>$map[strtolower($d)] ?? $d, $days);
                    echo htmlspecialchars(implode(', ', $labels), ENT_QUOTES, 'UTF-8');
                ?>
                &nbsp;·&nbsp;
                <?= $r['departure_time'] ? htmlspecialchars(substr($r['departure_time'],0,5), ENT_QUOTES, 'UTF-8') : '—' ?>
                </td>
              <td><?= (int)$r['available_seats'] ?> / <?= (int)$r['total_seats'] ?></td>
              <td>
                <?= e(trim(($r['make'] ?: '') . ' ' . ($r['model'] ?: '') . ' ' . ($r['year'] ?: ''))) ?: '-' ?>
              </td>
              <td>
                  <?= $r['seat_price'] !== null ? ('₡' . number_format((float)$r['seat_price'], 0, ',', '.')) : '—' ?>
              </td>
              <td class="actions">
                <a class="btn" href="./edit_ride.php?id=<?= (int)$r['id'] ?>">Edit</a>
                <form method="post" action="../DAO/ride_delete.php" onsubmit="return confirm('Delete this ride?');" style="display:inline;">
                  <input type="hidden" name="ride_id" value="<?= (int)$r['id'] ?>">
                  <button class="btn danger" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>

  <footer class="footer">
    <div class="footer-links">
      <a href="../Search_Rides/index.php">Home</a> |
      <a href="./my_rides.php">Rides</a> |
      <a href="../Bookings/index.php">Bookings</a>
    </div>
    <p class="footer-copy">© Aventones.com</p>
  </footer>
</body>
</html>
