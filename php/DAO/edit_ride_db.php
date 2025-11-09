<?php
require_once __DIR__ . '/../Actions/session_driver.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

$driver_id      = (int)($_SESSION['user_id'] ?? 0);
$ride_id        = isset($_POST['ride_id']) ? (int)$_POST['ride_id'] : 0;

$name           = trim($_POST['name'] ?? '');
$vehicle_id     = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null;
$origin         = trim($_POST['origin'] ?? '');
$destination    = trim($_POST['destination'] ?? '');
$departure_time = trim($_POST['departure_time'] ?? '');
$seat_price     = isset($_POST['seat_price']) ? (float)$_POST['seat_price'] : null;
$total_seats    = isset($_POST['total_seats']) ? (int)$_POST['total_seats'] : null;

$days = $_POST['days'] ?? [];
$allowed = ['mon','tue','wed','thu','fri','sat','sun'];
$days = array_values(array_intersect($days, $allowed));
$days_set = $days ? implode(',', $days) : null;

// Validaciones 
if ($driver_id <= 0 || $ride_id <= 0) {
  header('Location: ../Driver/my_rides.php?error=Invalid request');
  exit;
}
if (!$vehicle_id || $origin === '' || $destination === '' || $departure_time === '' || $seat_price === null || $total_seats === null) {
  header('Location: ../Driver/edit_ride.php?id='.$ride_id.'&error=Missing required fields');
  exit;
}
if ($days_set === null) {
  header('Location: ../Driver/edit_ride.php?id='.$ride_id.'&error=Select at least one day');
  exit;
}

// 1) Verificar que el ride pertenece al driver y obtener totales actuales
$sql = "SELECT id, total_seats, available_seats, driver_id FROM rides WHERE id=? AND driver_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $ride_id, $driver_id);
$stmt->execute();
$res = $stmt->get_result();
$ride = $res->fetch_assoc();
$stmt->close();

if (!$ride) {
  header('Location: ../Driver/my_rides.php?error=Ride not found');
  exit;
}

// Validar que el vehículo pertenezca al driver
$sqlC = "SELECT id FROM vehicles WHERE id=? AND user_id=?";
$stmtC = $conn->prepare($sqlC);
$stmtC->bind_param('ii', $vehicle_id, $driver_id);
$stmtC->execute();
$own = $stmtC->get_result()->num_rows > 0;
$stmtC->close();
if (!$own) {
  header('Location: ../Driver/edit_ride.php?id='.$ride_id.'&error=Invalid vehicle');
  exit;
}
$old_total = (int)$ride['total_seats'];
$old_avail = (int)$ride['available_seats'];
$delta     = $total_seats - $old_total;

$new_avail = $old_avail + $delta;
if ($new_avail < 0) $new_avail = 0;
if ($new_avail > $total_seats) $new_avail = $total_seats;

// Actualizar
$sqlU = "UPDATE rides
         SET vehicle_id=?, name=?, origin=?, destination=?,
             departure_time=?, days_set=?,
             seat_price=?, total_seats=?, available_seats=?
         WHERE id=? AND driver_id=?";
$stmtU = $conn->prepare($sqlU);
$stmtU->bind_param(
  'isssssdiisi',
  $vehicle_id, $name, $origin, $destination,
  $departure_time, $days_set,
  $seat_price, $total_seats, $new_avail,
  $ride_id, $driver_id
);

if (!$stmtU->execute()) {
  $err = $stmtU->error;
  $stmtU->close();
  header('Location: ../Driver/edit_ride.php?id='.$ride_id.'&error='.urlencode("DB error: $err"));
  exit;
}

$stmtU->close();
$conn->close();

header('Location: ../Driver/my_rides.php?msg=Ride updated successfully');
exit;
