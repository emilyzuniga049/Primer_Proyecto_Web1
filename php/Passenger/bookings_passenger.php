<?php
require_once __DIR__ . '/../Actions/session_passenger.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$passenger_id = (int)($_SESSION['user_id'] ?? 0);
if ($passenger_id <= 0) {
  header('Location: ../Index.html');
  exit;
}

$sql = "
  SELECT
    rsv.id        AS res_id,
    rsv.status    AS res_status,
    rsv.seats     AS res_seats,
    rsv.created_at,
    rd.id         AS ride_id,
    rd.name       AS ride_name,
    rd.origin, rd.destination,
    rd.departure_time, rd.days_set,
    rd.total_seats, rd.available_seats,
    v.make, v.model, v.year,
    du.id AS driver_id,
    du.first_name AS driver_first_name,
    du.last_name  AS driver_last_name,
    du.email      AS driver_email
  FROM reservations rsv
  JOIN rides rd        ON rd.id = rsv.ride_id
  LEFT JOIN vehicles v ON v.id = rd.vehicle_id
  JOIN users du        ON du.id = rd.driver_id
  WHERE rsv.passenger_id = ?
  ORDER BY rsv.created_at DESC, rsv.id DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $passenger_id);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function fmt_days($set){
  if(!$set) return '—';
  $map = ['mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat','sun'=>'Sun'];
  $out = [];
  foreach (explode(',', strtolower($set)) as $d) { $out[] = $map[$d] ?? $d; }
  return implode(', ', $out);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Aventones · My Bookings</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../css/estilos.css">
  <link rel="stylesheet" href="../../css/estilo_nav_logo.css">
  <link rel="stylesheet" href="../../css/driver_vehicles.css?v=3">
</head>
<body>
<header class="main-header">
  <div class="logo-container">
    <img src="../../Img/icono_carros.png" class="logo" alt="Logo"><h1>AVENTONES</h1>
  </div>
  <nav class="nav-bar">
    <a href="search_rides.php">Search Rides</a>
    <a href="bookings_passenger.php" class="active">Bookings</a>
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
  <?php if(isset($_GET['error'])): ?><div class="msg err"><?= e($_GET['error']) ?></div><?php endif; ?>
  <?php if(isset($_GET['msg'])): ?><div class="msg ok"><?= e($_GET['msg']) ?></div><?php endif; ?>

  <div class="card">
    <div class="section-head">
      <h2>My Reservations</h2>
    </div>

    <?php if(empty($rows)): ?>
      <p class="muted">You don't have reservations yet.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Driver</th>
            <th>Ride</th>
            <th>When</th>
            <th>Vehicle</th>
            <th>Seats</th>
            <th>Status</th>
            <th style="width:180px;">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?= (int)$r['res_id'] ?></td>

            <!-- Driver info -->
            <td>
              <?= e($r['driver_first_name'].' '.$r['driver_last_name']) ?><br>
              <small class="muted"><?= e($r['driver_email']) ?></small>
            </td>

            <!-- Ride info -->
            <td>
              <?= e($r['ride_name'] ?: ($r['origin'].' → '.$r['destination'])) ?><br>
              <small class="muted"><?= e($r['origin']) ?> → <?= e($r['destination']) ?></small>
            </td>

            <!-- When -->
            <td>
              <?= $r['departure_time'] ? e(substr($r['departure_time'],0,5)) : '—' ?><br>
              <small class="muted"><?= e(fmt_days($r['days_set'])) ?></small>
            </td>

            <!-- Vehicle -->
            <td>
              <?php
                $veh = trim(($r['make'].' '.$r['model'].' '.$r['year']));
                echo $veh ? e($veh) : '—';
              ?>
            </td>

            <!-- Seats -->
            <td><?= (int)$r['res_seats'] ?></td>

            <!-- Status -->
            <td>
              <?php
                $st = $r['res_status'];
                $badgeClass = 'badge pending';
                if ($st==='accepted') $badgeClass='badge active';
                if ($st==='rejected' || $st==='cancelled' || $st==='completed') $badgeClass='badge inactive';
              ?>
              <span class="<?= $badgeClass ?>"><?= e($st) ?></span>
            </td>

            <!-- Actions (Passenger can cancel pending/accepted) -->
            <td class="actions">
              <?php if ($r['res_status']==='pending' || $r['res_status']==='accepted'): ?>
                <form action="../DAO/bookings_passenger_canceled.php" method="post" onsubmit="return confirm('Do you want to cancel this reservation?')">
                  <input type="hidden" name="reservation_id" value="<?= (int)$r['res_id'] ?>">
                  <button class="btn danger sm" type="submit">Cancel</button>
                </form>
              <?php else: ?>
                <span class="muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
