<?php
require_once __DIR__ . '/../Actions/session_driver.php';
require_once __DIR__ . '/../Conexion/db_conexion.php';

$driver_id  = (int)($_SESSION['user_id'] ?? 0);
$vehicle_id = (int)($_POST['vehicle_id'] ?? 0);

if ($driver_id<=0 || $vehicle_id<=0) {
  header('Location: ../Driver/vehicles.php?error=Invalid+request'); exit;
}

$sql = "SELECT id FROM vehicles WHERE id=? AND user_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $vehicle_id, $driver_id);
$stmt->execute();
$own = $stmt->get_result()->num_rows > 0;
$stmt->close();
if (!$own) { header('Location: ../Driver/vehicles.php?error=Vehicle+not+found'); exit; }

$chkR = $conn->prepare("SELECT COUNT(*) c FROM rides WHERE vehicle_id=? AND status='active'");
$chkR->bind_param('i', $vehicle_id);
$chkR->execute();
$c = $chkR->get_result()->fetch_assoc()['c'] ?? 0;
$chkR->close();
if ($c > 0) {
  header('Location: ../Driver/vehicles.php?error=Vehicle+in+use+by+active+rides'); exit;
}

$del = $conn->prepare("DELETE FROM vehicles WHERE id=? AND user_id=?");
$del->bind_param('ii', $vehicle_id, $driver_id);
if (!$del->execute()) {
  $err = urlencode($del->error);
  $del->close(); $conn->close();
  header('Location: ../Driver/vehicles.php?error=DB+'.$err); exit;
}
$del->close(); $conn->close();

header('Location: ../Driver/vehicles.php?msg=Vehicle+deleted');
exit;
