<?php
require_once __DIR__ . '/../Actions/session_driver.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

$driverId = (int)($_SESSION['user_id'] ?? 0);
$rideId   = (int)($_POST['ride_id'] ?? 0);

if ($rideId <= 0) {
  header('Location: ../Driver/my_rides.php?error=Invalid ride');
  exit;
}

$sql = "DELETE FROM rides WHERE id = ? AND driver_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $rideId, $driverId);
$stmt->execute();
$stmt->close();

header('Location: ../Driver/my_rides.php?msg=Ride deleted');
exit;
