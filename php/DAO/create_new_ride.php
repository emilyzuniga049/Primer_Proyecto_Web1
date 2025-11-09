<?php
require_once __DIR__ . '/../Actions/session_driver.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

$driver_id     = (int)($_SESSION['user_id'] ?? 0);
$name          = trim($_POST['name'] ?? '');
$vehicle_id    = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null;
$origin        = trim($_POST['origin'] ?? '');
$destination   = trim($_POST['destination'] ?? '');
$departure_time= trim($_POST['departure_time'] ?? '');
$seat_price    = isset($_POST['seat_price']) ? (float)$_POST['seat_price'] : null;
$total_seats   = isset($_POST['total_seats']) ? (int)$_POST['total_seats'] : null;
$days          = $_POST['days'] ?? []; // array: ['mon','tue',...]


if (!$driver_id || !$vehicle_id || $origin === '' || $destination === '' || $departure_time === '' || $seat_price === null || $total_seats === null) {
  header('Location: ../Driver/new_ride.php?error=Missing required fields');
  exit;
}


$allowed = ['mon','tue','wed','thu','fri','sat','sun'];
$days = array_values(array_intersect($days, $allowed));
$days_set = $days ? implode(',', $days) : null;

if ($days_set === null) {
  header('Location: ../Driver/new_ride.php?error=Select at least one day');
  exit;
}


$sqlCheck = "SELECT id FROM vehicles WHERE id=? AND user_id=?";
$stmtC = $conn->prepare($sqlCheck);
$stmtC->bind_param('ii', $vehicle_id, $driver_id);
$stmtC->execute();
$resC = $stmtC->get_result();
if ($resC->num_rows === 0) {
  $stmtC->close();
  header('Location: ../Driver/new_ride.php?error=Invalid vehicle');
  exit;
}
$stmtC->close();


$sql = "INSERT INTO rides
(driver_id, vehicle_id, name, origin, destination,
 departure_time, days_set,
 seat_price, total_seats, available_seats, status)
VALUES (?,?,?,?,?,
        ?,?,
        ?, ?, ?, 'active')";

$stmt = $conn->prepare($sql);


if ($departure_time && strlen($departure_time) === 5) {
  $departure_time .= ':00';
}

$stmt->bind_param(
  'iisssssdii', 
  $driver_id, $vehicle_id, $name, $origin, $destination,
  $departure_time, $days_set,
  $seat_price, $total_seats, $total_seats
);

if (!$stmt->execute()) {
  $err = $stmt->error;
  $stmt->close();
  header('Location: ../Driver/new_ride.php?error=' . urlencode("DB error: $err"));
  exit;
}

$stmt->close();
$conn->close();

header('Location: ../Driver/my_rides.php?msg=Ride created successfully');
exit;

